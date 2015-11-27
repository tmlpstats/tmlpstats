<?php
namespace TmlpStats\Validate\Relationships;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Validate\ValidatorAbstract;

class DuplicateTeamMemberValidator extends ValidatorAbstract
{
    protected $sheetId = ImportDocument::TAB_CLASS_LIST;

    protected static $names = [];

    protected function validate($data)
    {
        $first = strtolower($data->firstName);
        $last = trim(str_replace('.', '', strtolower($data->lastName)));

        if (isset(static::$names[$first][$last])) {
            if (static::$names[$first][$last]) {
                $this->addMessage('CLASSLIST_DUPLICATE_TEAM_MEMBER', $data->firstName, $data->lastName);
                $this->isValid = false;
            }
        }

        static::$names[$first][$last][] = $data;

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
