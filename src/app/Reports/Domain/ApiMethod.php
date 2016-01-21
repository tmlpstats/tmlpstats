<?php namespace TmlpStats\Reports\Domain;

class ApiMethod {
    public $name;
    public $absName;
    public $params = [];

    public function __construct($name, $body) {
        $this->name = $name;
        $this->absName = $body['absName'];
        if (array_key_exists('params', $body)) {
            foreach ($body['params'] as $param) {
                $this->params[] = new ApiParameter($param);
            }
        }
    }

    public function absNameLocal() {
        return str_replace('.', '__', $this->absName);
    }

    public function absNameApiStyle() {
        $parts = explode('.', $this->absName);
        $output = '';
        $methodName = array_pop($parts);
        foreach($parts as $part){
            $output .= ucfirst($part) . '::';
        }
        $output .= $methodName;
        return $output;
    }
}
