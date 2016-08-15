<?php

namespace DiscoveryStats;

class SpecialSite extends Site {
    public function getFamily() {
        return 'special';
    }

    public function getCode() {
        return $this->data->code;
    }
}
