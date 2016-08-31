<?php
namespace TmlpStats\Validate\Objects;

use Respect\Validation\Validator as v;
use TmlpStats\Domain;
use TmlpStats\Traits;

class ApiScoreboardValidator extends ObjectsValidatorAbstract
{
    use Traits\ValidatesApiObjects;

    protected function populateValidators($data)
    {
        $intValidator           = v::intVal();
        $percentValidator       = v::numeric()->between(0, 100, true);

        // No validator for week since it is not set by user
        $this->dataValidators['cap'] = $intValidator;
        $this->dataValidators['cpc'] = $intValidator;
        $this->dataValidators['t1x'] = $intValidator;
        $this->dataValidators['t2x'] = $intValidator;
        $this->dataValidators['gitw'] = $percentValidator;
        $this->dataValidators['lf'] = v::oneOf(
            v::intVal()->equals(0),
            v::intVal()->positive()
        );
    }

    protected function validate($data)
    {
        if (!$this->validatePromises($data)) {
            $this->isValid = false;
        }
        if (!$this->validationActuals($data)) {
            $this->isValid = false;
        }

        return $this->isValid;
    }

    /**
     * Overridden default field validator
     *
     * @param  Traits\Referenceable $data  Data
     * @return bool                        True if valid
     */
    protected function validateFields($data)
    {
        return true;
    }

    protected function validatePromises($data)
    {
        $isValid = true;

        if (!$this->validateGames($data, 'promise', true)) {
            $isValid = false;
        }

        return $isValid;
    }

    protected function validationActuals($data)
    {
        $isValid = true;

        $required = $this->reportingDate->gte($data->week);

        if (!$this->validateGames($data, 'actual', $required)) {
            $isValid = false;
        }

        return $isValid;
    }

    protected function validateGames($data, $type, $required)
    {
        $isValid = true;

        foreach (Domain\Scoreboard::GAME_KEYS as $game) {
            $value = $data->game($game)->$type();

            $validator = $this->dataValidators[$game];
            if (!$validator->validate($value)) {
                $messageId = 'GENERAL_INVALID_VALUE';
                $displayName = strtoupper($game) . " {$type}";
                $params = $params = ['name' => $displayName, 'value' => $value];

                if ($value === null ) {
                    if (!$required) {
                        continue;
                    }
                    $messageId = 'GENERAL_MISSING_VALUE';
                    $params = ['name' => $displayName];
                }

                $this->messages[] = Domain\ValidationMessage::error([
                    'id' => $messageId,
                    'ref' => $data->getReference(['game' => $game, 'promiseType' => $type]),
                    'params' => $params,
                ]);
                $isValid = false;
            }
        }

        return $isValid;
    }
}
