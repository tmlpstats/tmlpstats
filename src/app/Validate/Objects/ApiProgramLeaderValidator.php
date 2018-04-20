<?php
namespace TmlpStats\Validate\Objects;

use App;
use Respect\Validation\Validator as v;
use TmlpStats\Api;
use TmlpStats\Traits;

class ApiProgramLeaderValidator extends ApiObjectsValidatorAbstract
{
    use Traits\ValidatesTravelWithConfig;

    protected $accountabilityMap = [
        'programManager' => 'Program Manager',
        'classroomLeader' => 'Classroom Leader',
    ];

    protected function populateValidators($data)
    {
        $nameValidator = v::stringType()->notEmpty();

        $this->dataValidators['firstName'] = $nameValidator;
        $this->dataValidators['lastName'] = $nameValidator;
        $this->dataValidators['phone'] = v::phone();
        $this->dataValidators['email'] = v::email();
        $this->dataValidators['accountability'] = v::in(array_keys($this->accountabilityMap));
        $this->dataValidators['attendingWeekend'] = v::optional(v::boolType());
    }

    protected function validate($data)
    {
        if (!$this->validateEmail($data)) {
            $this->isValid = false;
        }
        if (!$this->validateTravel($data)) {
            $this->isValid = false;
        }

        return $this->isValid;
    }

    protected function validateEmail($data)
    {
        $bouncedEmails = App::make(Api\Context::class)->getSetting('bouncedEmails');
        if (!$bouncedEmails) {
            return true;
        }

        $emails = explode(',', $bouncedEmails);
        if (in_array($data->email, $emails)) {
            $accountability = $this->accountabilityMap[$data->accountability] ?? $data->accountability;
            $this->addMessage('warning', [
                'id' => 'PROGRAMLEADER_BOUNCED_EMAIL',
                'ref' => $data->getReference(['field' => 'email']),
                'params' => [
                    'accountability' => $accountability,
                    'email' => $data->email,
                ],
            ]);
        }

        return true;
    }

    public function validateTravel($data)
    {
        // attendingWeekend must be reported starting after the configured date
        if (!$this->isTimeToCheckTravel()) {
            return true;
        }

        $isValid = true;
        if (is_null($data->attendingWeekend)) {
            $accountability = $this->accountabilityMap[$data->accountability] ?? $data->accountability;
            $this->addMessage('error', [
                'id' => 'PROGRAMLEADER_ATTENDING_WEEKEND_MISSING',
                'ref' => $data->getReference(['field' => 'attendingWeekend']),
                'params' => [
                    'accountability' => $accountability,
                ],
            ]);
            $isValid = false;
        }

        return $isValid;
    }
}
