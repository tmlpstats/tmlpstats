<?php
namespace TmlpStats\Validate\Objects;

use Carbon\Carbon;
use TmlpStats\Validate\ApiValidatorAbstract;
use TmlpStats\Util;

abstract class ApiObjectsValidatorAbstract extends ApiValidatorAbstract
{
    protected $dataValidators = [];
    protected $reader = null;
    protected $skipped = false;

    public function run($data, array $pastWeeks = [])
    {
        $this->data = $data;
        $this->populateValidators($data);
        $this->pastWeeks = $pastWeeks;

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
        if (!isset($this->dataValidators) || !$this->dataValidators) {
            return true;
        }

        $isValid = true;

        foreach ($this->dataValidators as $field => $validator) {
            $value = $data->$field;
            if (!$validator->validate($value)) {
                $displayName = ucwords(Util::toWords($field));
                $messageId = 'GENERAL_INVALID_VALUE';
                $params = ['name' => $displayName, 'value' => $value];

                if ($value === null || $value === '') {
                    $messageId = 'GENERAL_MISSING_VALUE';
                    $params = ['name' => $displayName];
                }

                $this->addMessage('error', [
                    'id' => $messageId,
                    'ref' => $data->getReference(['field' => $field]),
                    'params' => $params,
                ]);

                $isValid = false;
            }
        }

        return $isValid;
    }

    protected function getDateObject($date)
    {
        if (!$date || !preg_match("/^20\d\d-[0-1]\d-[0-3]\d$/", $date)) {
            return Util::parseUnknownDateFormat($date);
        }

        return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
    }
}
