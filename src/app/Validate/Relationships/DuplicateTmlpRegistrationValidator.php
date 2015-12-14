<?php
namespace TmlpStats\Validate\Relationships;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Validate\ValidatorAbstract;

class DuplicateTmlpRegistrationValidator extends ValidatorAbstract
{
    protected $sheetId = ImportDocument::TAB_WEEKLY_STATS;

    protected static $names = [];

    protected function validate($data)
    {
        $first = strtolower($data->firstName);
        $last  = trim(str_replace('.', '', strtolower($data->lastName)));

        if (isset(static::$names[$first][$last])) {
            if (static::$names[$first][$last]) {
                $this->addMessage('TMLPREG_DUPLICATE_NAME', $data->firstName, $data->lastName);
                $this->isValid = false;
            }
        }

        if (!$data->wd) {
            static::$names[$first][$last][] = $data;
        }

        return $this->isValid;
    }

    public function getWorkingData()
    {
        return ['names' => static::$names];
    }

    public function resetWorkingData()
    {
        static::$names = [];
    }
}
