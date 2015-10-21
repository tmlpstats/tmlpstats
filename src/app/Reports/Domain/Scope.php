<?php namespace TmlpStats\Reports\Domain;


class Scope {
    public $id;
    public $required_params;


    function __construct($id, $body) {
        $this->id = $id;
        $required_params = array();
        foreach ($body['required_params'] as $name => $p) {
            $required_params[] = new RequiredParam($name, $p);
        }
        $this->required_params = $required_params;
    }
}
