<?php

namespace DiscoveryStats;

abstract class Site {
    protected $data;

    public function __construct( $data ) {
        $this->data = $data;
    }

    public function getUrl() {
        return $this->data->url;
    }

    public function getName() {
        return $this->data->sitename;
    }

    public function getDbName() {
        return $this->data->dbname;
    }

    public function isPrivate() {
        return isset( $this->data->private ) && $this->data->private !== false;
    }

    public function isFishbowl() {
        return isset( $this->data->fishbowl ) && $this->data->fishbowl !== false;
    }

    public abstract function getFamily();

    public abstract function getCode();
}
