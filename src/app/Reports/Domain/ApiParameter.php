<?php namespace TmlpStats\Reports\Domain;

class ApiParameter {
    public $name;
    public $type;

    public function __construct($body) {
        $this->name = $body['name'];
        $this->type = $body['type'];
    }

    public function absNameLocal() {
        return str_replace('.', '__', $this->absName);
    }
}
