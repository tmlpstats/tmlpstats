<?php namespace TmlpStats\Reports\Arrangements;

class BaseArrangement {
    public $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function compose() {
        return $this->build($this->data);
    }
}
