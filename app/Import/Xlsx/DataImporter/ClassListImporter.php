<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Quarter;
use TmlpStats\TeamMember;
use TmlpStats\TeamMemberData;
use TmlpStats\CenterStats;
use TmlpStats\CenterStatsData;

use Carbon\Carbon;

use Log;

class ClassListImporter extends DataImporterAbstract
{
    protected $sheetId = ImportDocument::TAB_CLASS_LIST;

    protected $totalTdos = 0;
    protected $totalTeamMembersDoingTdo = 0;
    protected $totalTeamMembers = 0;

    protected $blockT1Q1 = array();
    protected $blockT1Q2 = array();
    protected $blockT1Q3 = array();
    protected $blockT1Q4 = array();

    protected $blockT2Q1 = array();
    protected $blockT2Q2 = array();
    protected $blockT2Q3 = array();
    protected $blockT2Q4 = array();

    protected function populateSheetRanges()
    {
        $t1q4 = $this->findRange(25, 'Team 1 Completing', 'Team 2 Completing');
        $this->blockT1Q4[] = $this->excelRange('A','S');
        $this->blockT1Q4[] = $this->excelRange($t1q4['start'] + 1, $t1q4['end']);

        $t2q4 = $this->findRange($t1q4['end'], 'Team 2 Completing', 'Current Team Completing');
        $this->blockT2Q4[] = $this->excelRange('A','S');
        $this->blockT2Q4[] = $this->excelRange($t2q4['start'] + 1, $t2q4['end']);

        $t1q3 = $this->findRange($t2q4['end'], 'Team 1 Completing', 'Team 2 Completing');
        $this->blockT1Q3[] = $this->excelRange('A','S');
        $this->blockT1Q3[] = $this->excelRange($t1q3['start'] + 1, $t1q3['end']);

        $t2q3 = $this->findRange($t1q3['end'], 'Team 2 Completing', 'Current Team Completing');
        $this->blockT2Q3[] = $this->excelRange('A','S');
        $this->blockT2Q3[] = $this->excelRange($t2q3['start'] + 1, $t2q3['end']);

        $t1q2 = $this->findRange($t2q3['end'], 'Team 1 Completing', 'Team 2 Completing');
        $this->blockT1Q2[] = $this->excelRange('A','S');
        $this->blockT1Q2[] = $this->excelRange($t1q2['start'] + 1, $t1q2['end']);

        $t2q2 = $this->findRange($t1q2['end'], 'Team 2 Completing', 'Current Team Completing');
        $this->blockT2Q2[] = $this->excelRange('A','S');
        $this->blockT2Q2[] = $this->excelRange($t2q2['start'] + 1, $t2q2['end']);

        $t1q1 = $this->findRange($t2q2['end'], 'Team 1 Completing', 'Team 2 Completing');
        $this->blockT1Q1[] = $this->excelRange('A','S');
        $this->blockT1Q1[] = $this->excelRange($t1q1['start'] + 1, $t1q1['end']);

        $t2q1 = $this->findRange($t1q1['end'], 'Team 2 Completing', 'Please e-mail the completed performance report to your Regional Statistician(s)');
        $this->blockT2Q1[] = $this->excelRange('A','S');
        $this->blockT2Q1[] = $this->excelRange($t2q1['start'] + 1, $t2q1['end'] - 4);
    }

    protected function load()
    {
        $this->reader = $this->getReader($this->sheet);

        $this->loadBlock($this->blockT1Q4, 1);
        $this->loadBlock($this->blockT2Q4, 2);
        $this->loadBlock($this->blockT1Q3, 1);
        $this->loadBlock($this->blockT2Q3, 2);
        $this->loadBlock($this->blockT1Q2, 1);
        $this->loadBlock($this->blockT2Q2, 2);
        $this->loadBlock($this->blockT1Q1, 1);
        $this->loadBlock($this->blockT2Q1, 2);
    }

    protected function loadBlock($blockParams, $teamYear=NULL)
    {
        foreach ($blockParams[1] as $row) {

            $completionQuarterRow  = $blockParams[1][0] - 2;
            $completionQuarterDate = $this->reader->getCompletionQuarter($completionQuarterRow);
            $this->loadEntry($row, array($teamYear, $completionQuarterDate));
        }
    }

    protected function loadEntry($row, $args)
    {
        if ($this->reader->isEmptyCell($row,'A')) return;

        $this->data[] = array(
            'centerId'          => $this->statsReport->center->id,
            'teamYear'          => $args[0],
            'completionQuarter' => $args[1],
            'firstName'         => $this->reader->getFirstName($row),
            'lastName'          => $this->reader->getLastInitial($row),
            'offset'            => $row,
            'wknd'              => $this->reader->getWknd($row),
            'xferOut'           => $this->reader->getXferOut($row),
            'xferIn'            => $this->reader->getXferIn($row),
            'ctw'               => $this->reader->getCtw($row),
            'wd'                => $this->reader->getWd($row),
            'wbo'               => $this->reader->getWbo($row),
            'rereg'             => $this->reader->getRereg($row),
            'excep'             => $this->reader->getExcep($row),
            'travel'            => $this->reader->getTravel($row),
            'room'              => $this->reader->getRoom($row),
            'comment'           => $this->reader->getComment($row),
            'accountability'    => $this->reader->getAccountability($row), // intentionally set twice to keep track of changes
            'gitw'              => $this->reader->getGitw($row),
            'tdo'               => $this->reader->getTdo($row),
        );
    }

    public function postProcess()
    {
        $totalTdos = 0;
        $totalTeamMembersDoingTdo = 0;
        $totalTeamMembers = 0;

        foreach ($this->data as $memberInput) {

            $completionQuarter = Quarter::region($this->statsReport->center->region)
                ->date($memberInput['completionQuarter'])
                ->first();

            if (!$completionQuarter) {
                Log::error("Completion quarter '{$memberInput['completionQuarter']}' in region '{$this->statsReport->center->globalRegion}' doesn't exist");
                continue;
            }

            $member = TeamMember::firstOrNew(array(
                'center_id'             => $memberInput['centerId'],
                'first_name'            => $memberInput['firstName'],
                'last_name'             => trim(str_replace('.', '', $memberInput['lastName'])),
                'team_year'             => $memberInput['teamYear'],
                'completion_quarter_id' => $completionQuarter->id,
            ));
            $member->accountability = $memberInput['accountability'];
            if ($member->statsReportId === null) {
                $member->statsReportId = $this->statsReport->id;
            }
            if ($member->isDirty()) {
                $member->save();
            }

            $memberData = TeamMemberData::firstOrNew(array(
                'center_id'       => $memberInput['centerId'],
                'quarter_id'      => $this->statsReport->quarter->id,
                'reporting_date'  => $this->statsReport->reportingDate->toDateString(),
                'team_member_id'  => $member->id,
                'stats_report_id' => $this->statsReport->id,
            ));

            // Unset unneeded data
            unset($memberInput['centerId']);
            unset($memberInput['firstName']);
            unset($memberInput['lastName']);
            unset($memberInput['teamYear']);
            unset($memberInput['completionQuarter']);

            $memberData = $this->setValues($memberData, $memberInput);
            $memberData->save();

            if ($memberData->wd || $memberData->wbo || $memberData->xferOut) continue;

            $tdo = preg_match('/^y$/i', $memberData->tdo) ? 1 : 0;
            if ($tdo > 0) {
                $totalTdos += $tdo;
                $totalTeamMembersDoingTdo++;
            }
            $totalTeamMembers++;
        }

        $centerStats = CenterStats::where('center_id', '=', $this->statsReport->center->id)
                                  ->where('reporting_date', '=', $this->statsReport->reportingDate->toDateString())
                                  ->first();

        if ($centerStats) {
            $data = CenterStatsData::find($centerStats->actualDataId);
            if ($data) {
                $tdoActual = 0;
                if ($totalTeamMembers > 0) {
                    $tdoActual = round(($totalTeamMembersDoingTdo/$totalTeamMembers)*100);
                }
                $data->tdo = $tdoActual;
                $data->save();
            }
        }
    }
}
