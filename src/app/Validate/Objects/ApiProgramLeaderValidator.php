<?php
namespace TmlpStats\Validate\Objects;

use Respect\Validation\Validator as v;
use TmlpStats\Settings\Setting;

class ApiProgramLeaderValidator extends ApiObjectsValidatorAbstract
{
    protected $accountabilityMap = [
        'programManager' => 'Program Manager',
        'classroomLeader' => 'Classroom Leader',
    ];

    protected function populateValidators($data)
    {
        $nameValidator = v::stringType()->notEmpty();

        $this->dataValidators['firstName']        = $nameValidator;
        $this->dataValidators['lastName']         = $nameValidator;
        $this->dataValidators['phone']            = v::phone();
        $this->dataValidators['email']            = v::email();
        $this->dataValidators['accountability']   = v::in(array_keys($this->accountabilityMap));
        $this->dataValidators['attendingWeekend'] = v::boolType();
    }

    protected function validate($data)
    {
        if (!$this->validateEmail($data)) {
            $this->isValid = false;
        }

        return $this->isValid;
    }

    protected function validateEmail($data)
    {
        $bouncedEmails = Setting::name('bouncedEmails')->get();
        if (!$bouncedEmails) {
            return true;
        }

        $emails = explode(',', $bouncedEmails);
        if (in_array($data->email, $emails)) {
            $accountability = $this->accountabilityMap[$data->accountability] ?? $data->accountability;
            $this->addMessage('PROGRAMLEADER_BOUNCED_EMAIL', $accountability, $data->email);
        }

        return true;
    }
}
