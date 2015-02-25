<?php
namespace TmlpStats\Validate;

use TmlpStats\Import\Xlsx\Reader as Reader;
use Respect\Validation\Validator as v;

class CenterStatsValidator extends ValidatorAbstract
{
    protected $classDisplayName = 'Current Weekly Stats';

    protected function populateValidators()
    {
        $intValidator           = v::int();
        $intNotNullValidator    = v::when(v::nullValue(), v::alwaysInvalid(), $intValidator);
        $rowIdValidator         = v::numeric()->positive();
        $rowIdOrNullValidator   = v::when(v::nullValue(), v::alwaysValid(), $rowIdValidator);
        $percentValidator       = v::numeric()->between(0, 100, true);
        $percentOrNullValidator = v::when(v::nullValue(), v::alwaysValid(), $percentValidator);

        $types = array('promise', 'actual');

        $this->dataValidators['reportingDate']        = v::date('Y-m-d');
        $this->dataValidators['promiseDataId']        = $rowIdValidator;
        $this->dataValidators['revokedPromiseDataId'] = $rowIdOrNullValidator;
        $this->dataValidators['actualDataId']         = $rowIdOrNullValidator;
        // Skipping center (auto-generated)
        // Skipping quarter (auto-generated)
        $this->dataValidators['statsReportId']        = $rowIdValidator;

        $this->dataValidators['type']                 = v::in($types);
        $this->dataValidators['tdo']                  = $percentOrNullValidator;
        $this->dataValidators['cap']                  = $intNotNullValidator;
        $this->dataValidators['cpc']                  = $intNotNullValidator;
        $this->dataValidators['t1x']                  = $intNotNullValidator;
        $this->dataValidators['t2x']                  = $intNotNullValidator;
        $this->dataValidators['gitw']                 = $percentValidator;
        $this->dataValidators['lf']                   = $intNotNullValidator;
    }

    protected function validate()
    {
        return $this->isValid;
    }
}
