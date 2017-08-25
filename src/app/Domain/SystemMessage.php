<?php
namespace TmlpStats\Domain;

use TmlpStats as Models;

/**
 * Models a team application
 */
class SystemMessage extends ParserDomain
{
    protected static $validProperties = [
        'id' => [
            'owner' => 'message',
            'type' => 'int',
        ],
        'section' => [
            'owner' => 'message',
            'type' => 'string',
        ],
        'region' => [
            'owner' => 'message',
            'type' => 'Region',
            'assignId' => true,
        ],
        'center' => [
            'owner' => 'message',
            'type' => 'Center',
            'assignId' => true,
        ],
        'active' => [
            'owner' => 'message',
            'type' => 'bool',
        ],

        // These are inside the data object
        'title' => [
            'owner' => 'data',
            'type' => 'string',
        ],
        'content' => [
            'owner' => 'data',
            'type' => 'string',
        ],
        'level' => [
            'owner' => 'data',
            'type' => 'string',
        ],
    ];

    public static function fromModel($model, $options = [])
    {
        $ignore = array_get($options, 'ignore', false);

        $obj = new static();

        foreach (static::$validProperties as $k => $v) {
            if ($ignore && array_get($ignore, $k, false)) {
                continue;
            }
            switch ($v['owner']) {
                case 'message':
                    if ($model) {
                        $obj->$k = $model->$k;
                    }
                    break;
                case 'data':
                    $obj->$k = array_get($model->data, $k, null);
                    break;
            }
        }

        return $obj;
    }

    public function fillModel($model, $only_set = true)
    {
        $newData = null;
        foreach ($this->_values as $k => $v) {
            if ($only_set && !array_key_exists($k, $this->_setValues)) {
                continue;
            }
            $conf = self::$validProperties[$k];
            switch ($conf['owner']) {
                case 'message':
                    $this->copyTarget($model, $k, $v, $conf);
                    break;
                case 'data':
                    if ($newData === null) {
                        $newData = [];
                    }
                    $newData[$k] = $v;
                    break;
            }
        }
        $model->data = $newData;
    }
}
