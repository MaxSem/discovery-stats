<?php

namespace DiscoveryStats;

use Liuggio\StatsdClient\StatsdClient;
use Liuggio\StatsdClient\Sender\SocketSender;
use Liuggio\StatsdClient\Service\StatsdService;

require_once( 'vendor/autoload.php' );

$statsd = null;

$wikiBlacklist = [
    'ukwikimedia', // redirected
];

$configFile = count( $argv ) > 1
    ? $argv[1]
    : 'config.json';

$config = json_decode( file_get_contents( $configFile ) );
$config->categories = (array)$config->categories;
$categoryKeys = array_keys( $config->categories );

if ( $config->statsdHost ) {
    $sender = new SocketSender( $config->statsdHost, $config->statsdHost, 'tcp' );
    $client = new StatsdClient( $sender );
    $statsd = new StatsdService( $client );
}

function recordToGraphite( $wiki, $metric, $count ) {
    global $statsd, $config;

    if ( !$statsd ) {
        return;
    }

    $key = str_replace( '%WIKI%', $wiki, $config->categories[$metric] );

    $statsd->set( $key, $count );
}

$matrix = new SiteMatrix;

$totalCounts = array_fill_keys( $categoryKeys, 0 );
foreach ( $matrix->getSites() as $site ) {
    if ( $site->isPrivate() || in_array( $site->getDbName(), $wikiBlacklist ) ) {
        continue;
    }
    $siteKey = $site->getFamily() . '.' . $site->getCode();
    $tracking = new TrackingCategories( $site );

    $counts = $tracking->getCounts( $categoryKeys );
    foreach ( $counts as $metric => $count ) {
        $totalCounts[$metric] += $count;
        recordToGraphite( $siteKey, $metric, $count );
    }
    echo "{$site->getDbName()} "; var_dump($counts);
}

foreach ( $totalCounts as $metric => $count ) {
    recordToGraphite( 'total', $metric, $count );
}
var_dump($totalCounts);

if ( $statsd ) {
    $statsd->flush();
}
