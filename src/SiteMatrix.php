<?php

namespace DiscoveryStats;

class SiteMatrix {
    private $sites = [];

    public function getSites() {
        if ( $this->sites ) {
            return $this->sites;
        }

        $matrix = Api::get( 'https://meta.wikimedia.org',
            [ 'action' => 'sitematrix' ]
        );
        $matrix = (array)$matrix->sitematrix;

        foreach ( $matrix['specials'] as $site ) {
            $this->sites[$site->dbname] = new SpecialSite( $site );
        }
        unset( $matrix['specials'] );
        unset( $matrix['count'] );

        foreach ( $matrix as $language ) {
            foreach ( $language->site as $site ) {
                $this->sites[$site->dbname] = new NormalSite( $site, $language->code );
            }
        }

        return $this->sites;
    }
}
