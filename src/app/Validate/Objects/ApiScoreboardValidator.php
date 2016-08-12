<?php
namespace TmlpStats\Validate\Objects;

use Respect\Validation\Validator as v;
use TmlpStats\Domain;
use TmlpStats\Traits;

class ApiScoreboardValidator extends CenterStatsValidator
{
    use Traits\GeneratesApiMessages;

    protected function populateValidators($data)
    {
        $this->dataValidators['week'] = v::date('Y-m-d');
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

        if ($this->reportingDate->gte($data->week)) {
            $required = true;
        }

        if (!$this->validateGames($data, 'actual', $required)) {
            $isValid = false;
        }

        return $isValid;
    }

    protected function validateGames($data, $type, $required)
    {
        $isValid = true;

        foreach (Domain\Scoreboard::GAME_KEYS as $game) {
            $validator = v::intVal();
            if ($game == 'gitw') {
                $validator = v::numeric()->between(0, 100, true);
            }

            $value = $data->game($game)->$type();

            if (!$validator->validate($value)) {
                $displayName = strtoupper($game) . " {$type}";
                if ($value === null ) {
                    if (!$required) {
                        continue;
                    }
                    $value = '[empty]';
                }

                $this->addMessage('INVALID_VALUE', $displayName, $value);
                $isValid = false;
            }
        }

        return $isValid;
    }
}
