<?php

namespace DiscoveryStats;

class NormalSite extends Site {
    private $langCode;

    public function __construct( $data, $langCode ) {
        $this->langCode = $langCode;
        parent::__construct( $data );
    }

    public function getFamily() {
        return $this->data->code;
    }

    public function getCode() {
        return $this->langCode;
    }
}
