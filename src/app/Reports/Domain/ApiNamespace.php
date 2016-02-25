<?php namespace TmlpStats\Reports\Domain;

/**
 * ApiNamespace is a container for methods and sub-namespaces
 */
class ApiNamespace
{

    /**
     * The name of this namespace
     * @var string
     */
    public $name = '';

    /**
     * The full path of this namespace, including parent namespaces in dotted form e.g. Parent.Name
     * @var string
     */
    public $absName = '';

    /**
     * All the child methods and namespaces of this namespace.
     * @var array
     */
    public $children = [];

    public function __construct($name, $body)
    {
        $this->name = $name;
        $this->absName = $body['absName'];
        $this->children = $body['children'];
    }

    /**
     * Return only methods inside this namespace.
     * @return array
     */
    public function methods()
    {
        $methods = [];
        foreach ($this->children as $child) {
            if ($child instanceof ApiMethod) {
                $child->isLast = false;
                $methods[] = $child;
            }
        }
        $methods[count($methods) - 1]->isLast = true;
        return $methods;
    }
}
