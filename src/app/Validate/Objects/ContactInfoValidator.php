<?php
namespace TmlpStats\Validate\Objects;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use Respect\Validation\Validator as v;
use TmlpStats\Setting;

class ContactInfoValidator extends ObjectsValidatorAbstract
{
    protected $sheetId = ImportDocument::TAB_LOCAL_TEAM_CONTACT;

    protected function populateValidators($data)
    {
        $accountabilities = array(
            'Program Manager',
            'Classroom Leader',
            'T-2 Leader',
            'T-1 Leader',
            'Team 2 Team Leader',
            'Team 1 Team Leader',
            'Statistician',
            'Statistician Apprentice',
            'Reporting Statistician',
        );

        $emailValidator = v::email();
        if ($data->accountability == 'Reporting Statistician') {
            $emailValidator = v::alwaysValid();
        } else if (preg_match('/^N\/?A$/i', $data->name)) {
            $this->skipped = true;
            return; // Skip rows with names == NA or N/A
        }

        $this->dataValidators['name']           = v::stringType()->regex('/^(.+)\s([^\s]+)$/i');
        $this->dataValidators['accountability'] = v::in($accountabilities);
        $this->dataValidators['phone']          = v::phone();
        $this->dataValidators['email']          = $emailValidator;
    }

    protected function validate($data)
    {
        if (!$this->validateName($data)) {
            $this->isValid = false;
        }
        if (!$this->validateEmail($data)) {
            $this->isValid = false;
        }

        return $this->isValid;
    }

    protected function validateName($data)
    {
        $isValid = true;

        if (strpos($data->name, '/') !== false) {
            $this->addMessage('CONTACTINFO_SLASHES_FOUND');
            $isValid = false;
        }

        if (strtolower($data->name) == 'not applicable' || !$data->name) {
            $this->addMessage('CONTACTINFO_NO_NAME', $data->accountability);
            $isValid = false;
        }

        return $isValid;
    }

    protected function validateEmail($data)
    {
        $isValid = true;

        $bouncedEmails = Setting::get('bouncedEmails');
        if ($bouncedEmails && $bouncedEmails->value) {
            $emails = explode(',', $bouncedEmails->value);
            if (in_array($data->email, $emails)) {
                $this->addMessage('CONTACTINFO_BOUNCED_EMAIL', $data->accountability, $data->email);
                $isValid = false;
            }
        }

        return $isValid;
    }
}
