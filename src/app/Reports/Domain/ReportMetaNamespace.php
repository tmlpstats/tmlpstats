<?php namespace TmlpStats\Reports\Domain;

/**
 * ApiNamespace is a container for methods and sub-namespaces
 */
class ReportMetaNamespace
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
    //public $absName = '';

    /**
     * All the child methods and namespaces of this namespace.
     * @var array
     */
    public $children = [];

    public $childrenFlat = [];

    public function __construct($id, $body)
    {
        $this->id = $id;
        $this->name = array_get($body, 'name', $id);
        $this->forwardParams = array_get($body, 'forwardParams', []);
        //$this->absName = $body['absName'];
        $this->childrenFlat = $body['childrenFlat'];
        $this->children = $body['children'];
    }

    /**
     * Return only methods inside this namespace.
     * @return array
     */
    public function flatReports()
    {
        $reports = [];
        foreach ($this->childrenFlat as $child) {
            if ($child->type == 'report') {
                $reports[] = $child;
            }
        }

        return $reports;
    }

    public function childrenIds()
    {
        return array_map(function ($child) {return $child->id;}, $this->children);
    }

    public function flatEs6Array()
    {
        $items = [];
        foreach ($this->childrenFlat as $n => $report) {
            $r = [
                'id' => $report->id,
                'n' => $n,
                'type' => $report->type,
                'name' => $report->name,
            ];
            if ($childrenIds = $report->childrenIds()) {
                $r['children'] = $childrenIds;
            }
            $items[$report->id] = $r;
        }

        return $items;
    }
}
