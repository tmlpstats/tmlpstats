<?php
namespace TmlpStats\Validate;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Import\Xlsx\Reader as Reader;
use Respect\Validation\Validator as v;

class TmlpCourseInfoValidator extends ValidatorAbstract
{
    protected $sheetId = ImportDocument::TAB_COURSES;

    protected function populateValidators($data)
    {
        $positiveIntValidator        = v::int()->min(0, true);
        $positiveIntNotNullValidator = v::when(v::nullValue(), v::alwaysInvalid(), $positiveIntValidator);
        $rowIdValidator              = v::numeric()->positive();

        $types = array(
            'Incoming T1',
            'Future T1',
            'Incoming T2',
            'Future T2',
        );

        $this->dataValidators['type']                   = v::in($types);
        // Skipping center (auto-generated)
        $this->dataValidators['statsReportId']          = $rowIdValidator;

        $this->dataValidators['reportingDate']          = v::date('Y-m-d');
        $this->dataValidators['tmlpGameId']             = $rowIdValidator;
        $this->dataValidators['quarterStartRegistered'] = $positiveIntNotNullValidator;
        $this->dataValidators['quarterStartApproved']   = $positiveIntNotNullValidator;
        // Skipping quarter (auto-generated)
    }

    protected function validate($data)
    {
        if (!$this->validateQuarterStartValues($data)) {
            $this->isValid = false;
        }

        return $this->isValid;
    }

    public function validateQuarterStartValues($data)
    {
        $isValid = true;

        if ($data->quarterStartApproved > $data->quarterStartRegistered) {
            $this->addMessage('TMLPCOURSE_QSTART_TER_LESS_THAN_QSTART_APPROVED');
            $isValid = false;
        }

        return $isValid;
    }
}
