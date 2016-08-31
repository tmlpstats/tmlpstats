<?php
namespace TmlpStats\Validate\Objects;

use TmlpStats\Util;

use Carbon\Carbon;
use TmlpStats\Validate\ValidatorAbstract;

abstract class ObjectsValidatorAbstract extends ValidatorAbstract
{
    protected $dataValidators = [];
    protected $reader = null;
    protected $skipped = false;

    public function run($data, $supplementalData = null)
    {
        $this->data             = $data;
        $this->supplementalData = $supplementalData;
        $this->populateValidators($data);

        if ($this->skipped) {
            return $this->isValid;
        }

        if (!$this->validateFields($data)) {
            $this->isValid = false;
        }

        $this->validate($data);

        return $this->isValid;
    }

    abstract protected function populateValidators($data);

    protected function validateFields($data)
    {
        $isValid = true;

        foreach ($this->dataValidators as $field => $validator) {
            $value = $data->$field;
            if (!$validator->validate($value)) {
                $displayName = $this->getValueDisplayName($field);
                if ($value === null || $value === '') {
                    $value = '[empty]';
                }

                $this->addMessage('INVALID_VALUE', $displayName, $value);
                $isValid = false;
            }
        }

        return $isValid;
    }

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
