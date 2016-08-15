<?php

namespace DiscoveryStats;

class Api {
    public static function get( $url, $params ) {
        $params['format'] = 'json';
        $params['formatversion'] = 2;

        $arr = [];
        foreach ( $params as $key => $value ) {
            $arr[] = $key . '=' . urlencode( $value );
        }
        $paramsStr = implode( '&', $arr );

        return json_decode( file_get_contents( "{$url}/w/api.php?{$paramsStr}" ) );
    }
}

ini_set( 'user_agent', 'Discovery team statistics' );
