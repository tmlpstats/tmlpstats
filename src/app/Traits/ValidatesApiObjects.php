<?php
namespace TmlpStats\Traits;

use TmlpStats\Domain;

trait ValidatesApiObjects
{
    protected function validateFields($data)
    {
        if (!isset($this->dataValidators) || !$this->dataValidators) {
            return true;
        }

        $isValid = true;

        foreach ($this->dataValidators as $field => $validator) {
            $value = $data->$field;
            if (!$validator->validate($value)) {
                $displayName = $this->getValueDisplayName($field);
                $messageId = 'GENERAL_INVALID_VALUE';
                $params = ['name' => $displayName, 'value' => $value];

                if ($value === null || $value === '') {
                    $messageId = 'GENERAL_MISSING_VALUE';
                    $params = ['name' => $displayName];
                }

                $this->messages[] = Domain\ValidationMessage::error([
                    'id' => $messageId,
                    'ref' => $data->getReference(['field' => $field]),
                    'params' => $params,
                ]);

                $isValid = false;
            }
        }

        return $isValid;
    }
}
