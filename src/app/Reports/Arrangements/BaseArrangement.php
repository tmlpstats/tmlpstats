<?php namespace TmlpStats\Reports\Arrangements;

class BaseArrangement
{
    public $data;

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    public function compose($data = null)
    {
        if ($data === null) {
            $data = $this->data;
        }
        return $this->build($data);
    }
}
