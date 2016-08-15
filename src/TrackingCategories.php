<?php

namespace DiscoveryStats;

class TrackingCategories {
    private $site;

    public function __construct( Site $site ) {
        $this->site = $site;
    }

    public function getCounts( array $categories ) {
        $mapping = $this->getCategoryNames( $categories );
        $inverseMapping = array_flip( $mapping );

        $titles = implode( '|', array_map(
            function( $name ) {
                return "Category:$name";
            },
            array_values( $mapping )
        ) );
        $result = Api::get( $this->site->getUrl(), [
            'action' => 'query',
            'prop' => 'categoryinfo',
            'titles' => $titles,
        ] );

        $counts = [];
        foreach ( $result->query->pages as $page ) {
            if ( !isset( $page->categoryinfo ) ) {
                continue;
            }
            list( , $cat ) = explode( ':', $page->title, 2 );
            $counts[ $inverseMapping[$cat] ] = $page->categoryinfo->size;
        }

        return $counts;
    }

    private function getCategoryNames( array $categories ) {
        // Get local tracking category name. Parse it because it might contain
        // wikitext e.g. {{#ifeq:{{NAMESPACE}}||Articles with maps|Pages with maps}}.
        // In case such difference is present, care about mainspace only.
        $wikitext = implode( "\n\n", array_map(
            function( $category ) {
                return "$category={{int:$category}}";
            },
            $categories
        ) );
        $siteinfo = Api::get( $this->site->getUrl(), [
            'action' => 'parse',
            'title' => 'foo',
            'contentmodel' => 'wikitext',
            'text' => $wikitext,
        ] );

        $decoded = trim( htmlspecialchars_decode( strip_tags( $siteinfo->parse->text ) ) );
        $mapping = [];
        $lines = explode( "\n", $decoded );
        foreach ( $lines as $line ) {
            list( $key, $category ) = explode( '=', trim( $line ), 2 );
            if ( !$category || !in_array( $key, $categories ) ) {
                throw new \Exception( "{$this->site->getDbName()} returned an undexpected response: $decoded" );
            }
            if ( $category[0] == '<' ) {
                continue; // Extension not installed
            }
            $mapping[$key] = $category;
        }

        return $mapping;
    }
}
