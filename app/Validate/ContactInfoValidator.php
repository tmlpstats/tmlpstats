<?php
namespace TmlpStats\Validate;

use Respect\Validation\Validator as v;

class ContactInfoValidator extends ValidatorAbstract
{
    protected $classDisplayName = 'Local Team Contact Info.';

    protected function populateValidators()
    {
        $nameValidator  = v::string()->notEmpty();
        $rowIdValidator = v::numeric()->positive();

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

        $this->dataValidators['firstName']      = $nameValidator;
        $this->dataValidators['lastName']       = $nameValidator;
        $this->dataValidators['accountability'] = v::in($accountabilities);
        $this->dataValidators['phone']          = v::phone();
        $this->dataValidators['email']          = v::email();
        // Skipping center (auto-generated)
        // Skipping quarter (auto-generated)
        $this->dataValidators['statsReportId']  = $rowIdValidator;
    }

    protected function validate()
    {
        return $this->isValid;
    }
}
