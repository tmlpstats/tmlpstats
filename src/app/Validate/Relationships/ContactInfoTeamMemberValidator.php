<?php
namespace TmlpStats\Validate\Relationships;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Util;
use TmlpStats\Validate\ValidatorAbstract;

class ContactInfoTeamMemberValidator extends ValidatorAbstract
{
    protected $sheetId = ImportDocument::TAB_LOCAL_TEAM_CONTACT;

    protected function validate($data)
    {
        if ($data->accountability == 'Program Manager'
            || $data->accountability == 'Classroom Leader'
            || !$data->name
            || $data->name == 'NA'
            || $data->name == 'N/A'
        ) {
            return $this->isValid;
        }

        $nameParts = Util::getNameParts($data->name);

        $first = strtolower($nameParts['firstName']);
        $last = strtolower($nameParts['lastName']);

        if (!isset($this->supplementalData['names'][$first][$last])) {
            $this->addMessage('CONTACTINFO_NO_MATCHING_TEAM_MEMBER', $data->name, $data->accountability);
        }

        return $this->isValid;
    }
}
