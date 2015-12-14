<?php
namespace TmlpStats\Validate\Objects;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use Respect\Validation\Validator as v;

class CenterStatsValidator extends ObjectsValidatorAbstract
{
    protected $sheetId = ImportDocument::TAB_WEEKLY_STATS;

    protected function populateValidators($data)
    {
        $intValidator           = v::intVal();
        $percentValidator       = v::numeric()->between(0, 100, true);
        $percentOrNullValidator = v::optional($percentValidator);

        $types = ['promise', 'actual'];

        $this->dataValidators['reportingDate'] = v::date('Y-m-d');
        $this->dataValidators['type']          = v::in($types);
        $this->dataValidators['tdo']           = $percentOrNullValidator;
        $this->dataValidators['cap']           = $intValidator;
        $this->dataValidators['cpc']           = $intValidator;
        $this->dataValidators['t1x']           = $intValidator;
        $this->dataValidators['t2x']           = $intValidator;
        $this->dataValidators['gitw']          = $percentValidator;
        $this->dataValidators['lf']            = $intValidator;
    }

    protected function validate($data)
    {
        return $this->isValid;
    }
}
