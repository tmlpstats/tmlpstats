<?php namespace TmlpStats\Reports\Domain;

class Report {
    public $id;
    public $scope;
    public $name;
    public $desc;
    public $ticket;

    public function __construct($id, $body) {
        $this->id = $id;
        $this->name = $body['name'];
        $this->desc = (array_key_exists('desc', $body))? $body['desc'] : '';
        $this->access = $body['access'];
        $this->scope = $body['scope'];
        $this->ticket = (array_key_exists('ticket', $body))? $body['ticket'] : null;
        $this->questions = (array_key_exists('questions', $body))? $body['questions'] : [];
    }
}
