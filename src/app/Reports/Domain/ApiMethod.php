<?php
namespace TmlpStats\Reports\Domain;

/**
 * ApiMethod is a single method inside of an API namespace.
 * It has a name, some attributes, and parameters.
 */
class ApiMethod
{
    /**
     * The name of this method
     * @var string
     */
    public $name = '';
    /**
     * The absolute name, including the namespace, in dotted form. e.g. FooNamespace.SubNamespace.methodName
     * @var string
     */
    public $absName = '';

    public $params = [];

    public $isLast = false;

    public $desc = '';

    public $access = '';

    public function __construct($name, $body)
    {
        $this->name = $name;
        $this->absName = $body['absName'];
        if (isset($body['params'])) {
            foreach ($body['params'] as $param) {
                $this->params[] = new ApiParameter($param);
            }
            if (count($this->params) > 0) {
                $this->params[count($this->params) - 1]->isLast = true;
            }
        }
        if (isset($body['desc'])) {
            $this->desc = $body['desc'];
        }
        if (isset($body['access'])) {
            $this->access = $body['access'];
        }
    }

    public function absNameLocal()
    {
        return str_replace('.', '__', $this->absName);
    }

    public function absNameApiStyle()
    {
        $parts = explode('.', $this->absName);
        $output = '';
        $methodName = array_pop($parts);
        if (count($parts) > 0) {
            foreach ($parts as $part) {
                $output .= '\\' . ucfirst($part);
            }
        }
        $output .= '::' . $methodName;

        return substr($output, 1);
    }

    public function packageName()
    {
        $v = $this->absNameApiStyle();
        $parts = explode('::', $v);

        return $parts[0];
    }

    public function briefDescription()
    {
        return trim($this->desc); // TODO
    }
}
