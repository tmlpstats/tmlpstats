<?php
namespace TmlpStats\Validate;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use Respect\Validation\Validator as v;

class ContactInfoValidator extends ValidatorAbstract
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

        $this->dataValidators['name']           = v::string()->regex('/^(.+)\s([^\s]+)$/i');
        $this->dataValidators['accountability'] = v::in($accountabilities);
        $this->dataValidators['phone']          = v::phone();
        $this->dataValidators['email']          = v::email();
    }

    protected function validate($data)
    {
        return $this->isValid;
    }
}
