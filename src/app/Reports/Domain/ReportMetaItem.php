<?php namespace TmlpStats\Reports\Domain;

/**
 * ApiNamespace is a container for methods and sub-namespaces
 */
class ReportMetaItem
{
    public $id = '';
    public $uriSlug = '';

    /**
     * The name of this namespace
     * @var string
     */
    public $name = '';

    /**
     * The full path of this namespace, including parent namespaces in dotted form e.g. Parent.Name
     * @var string
     */
    //public $absName = '';

    /**
     * All the child methods and namespaces of this namespace.
     * @var array
     */
    public $children = [];

    public function __construct($id, $body)
    {
        $this->id = $id;
        $this->uriSlug = array_get($body, 'uriSlug', strtolower($id));
        $this->name = $body['name'];
        $this->shortName = array_get($body, 'shortName', null);
        $this->type = array_get($body, 'type', 'report');
        $this->children = array_get($body, 'children', []);
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
        if (count($methods)) {
            $methods[count($methods) - 1]->isLast = true;
        }

        return $methods;
    }

    public function childrenIds()
    {
        return array_map(function ($child) {return $child->id;}, $this->children);
    }

    public function controllerFuncName()
    {
        return $this->id;
    }
}
