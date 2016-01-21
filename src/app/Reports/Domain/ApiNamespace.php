<?php namespace TmlpStats\Reports\Domain;

class ApiNamespace {
    public $name;
    public $absName;
    public $children;

    public function __construct($name, $body) {
        $this->name = $name;
        $this->absName = $body['absName'];
        $this->children = $body['children'];
    }
}
