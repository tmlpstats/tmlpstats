<?php
namespace TmlpStats\Import\Xlsx\Reader;

use TmlpStats\Util;
use Carbon\Carbon;

abstract class ReaderAbstract
{
    protected $data = NULL;
    protected $dataMap = array(
        // Map entry format. You may ommit field that does not apply. e.g. if a row is always passed
        // 'name' => array(
        //     'row'    => (NULL|number|array(numbers)),
        //     'col'    => (NULL|string|array(strings)),
        //     'format' => (NULL|date|phone)
        // );
    );

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function isEmptyCell($row, $col)
    {
        $cellData = $this->data[$row][$col];
        return ($cellData === NULL || trim($cellData) == '');
    }

    public function __call($method, $args)
    {
        if (preg_match("/^get(\w+)Row$/", $method, $matches))
        {
            $name = lcfirst($matches[1]);
            return $this->getRow($name);
        }
        else if (preg_match("/^get(\w+)Col$/", $method, $matches))
        {
            $name = lcfirst($matches[1]);
            return $this->getCol($name);
        }
        else if (preg_match("/^get(\w+)$/", $method, $matches))
        {
            $name = lcfirst($matches[1]);

            $argc = count($args);
            switch($argc)
            {
                case 1:
                    $row = $args[0];
                    $col = $this->getCol($name);
                    break;
                case 2:
                    $row = $args[0];
                    $col = $args[1];
                    break;
                default:
                    $row = $this->getRow($name);
                    $col = $this->getCol($name);
                    break;
            }

            // Catch developer errors
            if (!is_numeric($row))
            {
                throw new \Exception("[$name] Row must be numeric. Value provided is $row. To use array type, define method explicitly");
            }
            if (!ctype_alpha($col))
            {
                throw new \Exception("[$name] Col must be a letter. Value provided is $col. To use array type, define method explicitly");
            }
            if (is_null($row))
            {
                throw new \Exception("[$name] Row is NULL. Check that method is called correctly and the dataMap entry is correct.");
            }
            if (is_null($col))
            {
                throw new \Exception("[$name] Col is NULL. Check that method is called correctly and the dataMap entry is correct.");
            }

            $value = $this->getValue($row, $col);

            // Ignore cells with only whitespace. Using trim() converts NULL to '', so do it this way instead.
            if ($value === NULL || preg_match("/^[\s\v\h]*$/", $value))
            {
                $value = NULL;
            }

            if ($value)
            {
                $value = trim($value);
            }

            $format = $this->getFormat($name);
            if ($format)
            {
                switch($format)
                {
                    case 'date':
                        if (!is_null($value))
                        {
                            $value = Util::getExcelDate($value);
                        }
                        break;
                    case 'phone':
                        if (!is_null($value))
                        {
                            $value = preg_replace('/\D/', '', $value);
                            if(preg_match('/^(\d\d\d)(\d\d\d)(\d\d\d\d)$/', $value, $matches))
                            {
                                $value = "({$matches[1]}) {$matches[2]}-{$matches[3]}";
                            }
                        }
                        break;
                }
            }

            $callback = $this->getCallback($name);
            if ($callback)
            {
                $value = $this->$callback($name, $value);
            }

            return $value;
        }
        else
        {
            throw new \Exception("Invalid method called: '$method'");
        }

        return NULL;
    }

    protected function getRow($name)
    {
        $row = NULL;
        if (array_key_exists($name, $this->dataMap) && array_key_exists('row', $this->dataMap[$name]))
        {
            $row = $this->dataMap[$name]['row'];
        }
        return $row;
    }
    protected function getCol($name)
    {
        $col = NULL;
        if (array_key_exists($name, $this->dataMap) && array_key_exists('col', $this->dataMap[$name]))
        {
            $col = $this->dataMap[$name]['col'];
        }
        return $col;
    }
    protected function getFormat($name)
    {
        $format = NULL;
        if (array_key_exists($name, $this->dataMap) && array_key_exists('format', $this->dataMap[$name]))
        {
            $format = $this->dataMap[$name]['format'];
        }
        return $format;
    }
    protected function getCallback($name)
    {
        $callback = NULL;
        if (array_key_exists($name, $this->dataMap) && array_key_exists('callback', $this->dataMap[$name]))
        {
            $callback = $this->dataMap[$name]['callback'];
        }
        return $callback;
    }

    public function getValue($row, $col)
    {
        return $this->data[$row][$col];
    }

}
