<?php

if ( count( $argv ) < 2 ) {
    echo 'Usage: php tracking-category-count.php <tracking category message key> [graphite key]';
    die( 1 );
}

$trackingCategoryKey = $argv[1];
$graphiteKey = isset( $argv[2] ) ? $argv[2] : null;

ini_set( 'user_agent', 'Discovery team statistics' );

function apiGet( $url, $params ) {
    $params['format'] = 'json';
    $params['formatversion'] = 2;

    $arr = [];
    foreach ( $params as $key => $value ) {
        $arr[] = $key . '=' . urlencode( $value );
    }
    $paramsStr = implode( '&', $arr );

    return file_get_contents( "{$url}/w/api.php?{$paramsStr}" );
}

function recordToGraphite( $dbname, $count ) {
    global $graphiteKey;

    if ( !$graphiteKey ) {
        return;
    }

    $key = str_replace( '%WIKI%', $dbname, $graphiteKey );

    exec( "echo \"$key $count `date +%s`\" | nc -q0 $host $port" );
}

// Skip header
$wikis = array_slice( file( 'wikis.tsv' ), 1 );

$total = 0;
foreach ( $wikis as $line ) {
    list( $dbname, $url, ) = explode( "\t", $line );

    if ( $url == 'NULL' ) {
        continue; // https://phabricator.wikimedia.org/T142759
    }

    // Get local tracking category name. Parse it because it might contain
    // wikitext e.g. {{#ifeq:{{NAMESPACE}}||Articles with maps|Pages with maps}}.
    // In case such difference is present, care about mainspace only.
    $siteinfo = json_decode( apiGet( $url, [
        'action' => 'parse',
        'title' => 'foo',
        'contentmodel' => 'wikitext',
        'text' => "{{int:$trackingCategoryKey}}",
    ] ) );

    $category = trim( htmlspecialchars_decode( strip_tags( $siteinfo->parse->text ) ) );
    if ( $category[0] == '<' ) {
        continue; // Extension not installed
    }

    $categoryInfo = json_decode( apiGet( $url, [
        'action' => 'query',
        'prop' => 'categoryinfo',
        'titles' => "Category:$category",
    ] ) );

    $count = isset( $categoryInfo->query->pages[0]->categoryinfo )
        ? $categoryInfo->query->pages[0]->categoryinfo->size
        : 0;

    $total += $count;

    echo "$dbname $category $count\n";
    recordToGraphite( $dbname, $count );
}

recordToGraphite( 'total', $total );
