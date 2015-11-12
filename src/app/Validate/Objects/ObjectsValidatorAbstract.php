<?php
namespace TmlpStats\Validate\Objects;

use TmlpStats\StatsReport;
use TmlpStats\Util;

use Carbon\Carbon;
use TmlpStats\Validate\ValidatorAbstract;

abstract class ObjectsValidatorAbstract extends ValidatorAbstract
{
    protected $dataValidators = array();
    protected $reader = null;

    public function run($data)
    {
        $this->data = $data;
        $this->populateValidators($data);

        foreach ($this->dataValidators as $field => $validator)
        {
            $value = $this->data->$field;
            if (!$validator->validate($value))
            {
                $displayName = $this->getValueDisplayName($field);
                if ($value === null || $value === '')
                {
                    $value = '[empty]';
                }

                $this->addMessage('INVALID_VALUE', $displayName, $value);
                $this->isValid = false;
            }
        }

        $this->validate($data);
        return $this->isValid;
    }

    abstract protected function populateValidators($data);

    protected function getValueDisplayName($value)
    {
        return ucwords(Util::toWords($value));
    }

    protected function getDateObject($date)
    {
        if (!$date || !preg_match("/^20\d\d-[0-1]\d-[0-3]\d$/", $date)) {
            return Util::parseUnknownDateFormat($date);
        }
        return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
    }
}
