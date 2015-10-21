<?php namespace TmlpStats\Reports\Domain;


class RequiredParam {
    public $name;
    public $type;

    function __construct($name, $body) {
        $this->name = $name;
        $this->type = $body['type'];
    }
}
