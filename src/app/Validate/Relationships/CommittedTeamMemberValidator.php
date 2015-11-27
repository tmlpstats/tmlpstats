<?php
namespace TmlpStats\Validate\Relationships;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Util;
use TmlpStats\Validate\ValidatorAbstract;

class CommittedTeamMemberValidator extends ValidatorAbstract
{
    protected $sheetId = ImportDocument::TAB_WEEKLY_STATS;

    protected function validate($data)
    {
        if (!$data->committedTeamMemberName) {
            return $this->isValid;
        }

        $nameParts = Util::getNameParts($data->committedTeamMemberName);

        $first = strtolower($nameParts['firstName']);
        $last = strtolower($nameParts['lastName']);

        if (!isset($this->supplementalData['names'][$first][$last])) {
            $this->addMessage('TMLPREG_COMMITTED_TEAM_MEMBER_NO_MATCHING_TEAM_MEMBER', $data->committedTeamMemberName);
        }

        return $this->isValid;
    }
}
