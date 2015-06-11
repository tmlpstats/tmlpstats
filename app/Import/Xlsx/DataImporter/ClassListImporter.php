<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Quarter;
use TmlpStats\TeamMember;
use TmlpStats\TeamMemberData;
use TmlpStats\CenterStats;
use TmlpStats\CenterStatsData;

use Carbon\Carbon;

class ClassListImporter extends DataImporterAbstract
{
    protected $sheetId = ImportDocument::TAB_CLASS_LIST;

    protected $totalTdos = 0;
    protected $totalTeamMembersDoingTdo = 0;
    protected $totalTeamMembers = 0;

    protected static $blockT1Q1 = array();
    protected static $blockT1Q2 = array();
    protected static $blockT1Q3 = array();
    protected static $blockT1Q4 = array();

    protected static $blockT2Q1 = array();
    protected static $blockT2Q2 = array();
    protected static $blockT2Q3 = array();
    protected static $blockT2Q4 = array();

    protected function populateSheetRanges()
    {
        $t1q4 = $this->findRange(25, 'Team 1 Completing', 'Team 2 Completing');
        self::$blockT1Q4[] = $this->excelRange('A','S');
        self::$blockT1Q4[] = $this->excelRange($t1q4['start'] + 1, $t1q4['end']);

        $t2q4 = $this->findRange($t1q4['end'], 'Team 2 Completing', 'Current Team Completing');
        self::$blockT2Q4[] = $this->excelRange('A','S');
        self::$blockT2Q4[] = $this->excelRange($t2q4['start'] + 1, $t2q4['end']);

        $t1q3 = $this->findRange($t2q4['end'], 'Team 1 Completing', 'Team 2 Completing');
        self::$blockT1Q3[] = $this->excelRange('A','S');
        self::$blockT1Q3[] = $this->excelRange($t1q3['start'] + 1, $t1q3['end']);

        $t2q3 = $this->findRange($t1q3['end'], 'Team 2 Completing', 'Current Team Completing');
        self::$blockT2Q3[] = $this->excelRange('A','S');
        self::$blockT2Q3[] = $this->excelRange($t2q3['start'] + 1, $t2q3['end']);

        $t1q2 = $this->findRange($t2q3['end'], 'Team 1 Completing', 'Team 2 Completing');
        self::$blockT1Q2[] = $this->excelRange('A','S');
        self::$blockT1Q2[] = $this->excelRange($t1q2['start'] + 1, $t1q2['end']);

        $t2q2 = $this->findRange($t1q2['end'], 'Team 2 Completing', 'Current Team Completing');
        self::$blockT2Q2[] = $this->excelRange('A','S');
        self::$blockT2Q2[] = $this->excelRange($t2q2['start'] + 1, $t2q2['end']);

        $t1q1 = $this->findRange($t2q2['end'], 'Team 1 Completing', 'Team 2 Completing');
        self::$blockT1Q1[] = $this->excelRange('A','S');
        self::$blockT1Q1[] = $this->excelRange($t1q1['start'] + 1, $t1q1['end']);

        $t2q1 = $this->findRange($t1q1['end'], 'Team 2 Completing', 'Please e-mail the completed performance report to your Regional Statistician(s)');
        self::$blockT2Q1[] = $this->excelRange('A','S');
        self::$blockT2Q1[] = $this->excelRange($t2q1['start'] + 1, $t2q1['end'] - 4);
    }

    public function getTdo()
    {
        $tdoActual = 0;
        if ($this->totalTeamMembers > 0) {
            $tdoActual = round(($this->totalTeamMembersDoingTdo/$this->totalTeamMembers)*100);
        }
        return $tdoActual;
    }

    protected function load()
    {
        $this->reader = $this->getReader($this->sheet);

        $this->loadBlock(self::$blockT1Q4, 1);
        $this->loadBlock(self::$blockT2Q4, 2);
        $this->loadBlock(self::$blockT1Q3, 1);
        $this->loadBlock(self::$blockT2Q3, 2);
        $this->loadBlock(self::$blockT1Q2, 1);
        $this->loadBlock(self::$blockT2Q2, 2);
        $this->loadBlock(self::$blockT1Q1, 1);
        $this->loadBlock(self::$blockT2Q1, 2);
    }

    protected function loadBlock($blockParams, $teamYear=NULL)
    {
        foreach($blockParams[1] as $row) {

            $completionQuarterRow  = $blockParams[1][0] - 2;
            $completionQuarterDate = $this->reader->getCompletionQuarter($completionQuarterRow);
            $this->loadEntry($row, array($teamYear, $completionQuarterDate));
        }
    }

    protected function loadEntry($row, $args)
    {
        if ($this->reader->isEmptyCell($row,'A')) return;

        $teamYear          = $args[0];
        $completionQuarter = Quarter::findByDateAndRegion($args[1], $this->statsReport->center->globalRegion);

        $member = TeamMember::firstOrCreate(array(
            'center_id'             => $this->statsReport->center->id,
            'first_name'            => $this->reader->getFirstName($row),
            'last_name'             => trim(str_replace('.', '', $this->reader->getLastInitial($row))),
            'team_year'             => $teamYear,
            'completion_quarter_id' => $completionQuarter->id,
        ));
        $member->accountability = $this->reader->getAccountability($row);
        if ($member->statsReportId === null) {
            $member->statsReportId = $this->statsReport->id;
        }
        $member->save();

        $memberData = TeamMemberData::firstOrCreate(array(
            'center_id'      => $this->statsReport->center->id,
            'quarter_id'     => $this->statsReport->quarter->id,
            'reporting_date' => $this->statsReport->reportingDate->toDateString(),
            'team_member_id' => $member->id,
        ));
        $memberData->offset         = $row;
        $memberData->wknd           = $this->reader->getWknd($row);
        $memberData->xferOut        = $this->reader->getXferOut($row);
        $memberData->xferIn         = $this->reader->getXferIn($row);
        $memberData->ctw            = $this->reader->getCtw($row);
        $memberData->wd             = $this->reader->getWd($row);
        $memberData->wbo            = $this->reader->getWbo($row);
        $memberData->rereg          = $this->reader->getRereg($row);
        $memberData->excep          = $this->reader->getExcep($row);
        $memberData->travel         = $this->reader->getTravel($row);
        $memberData->room           = $this->reader->getRoom($row);
        $memberData->comment        = $this->reader->getComment($row);
        $memberData->accountability = $this->reader->getAccountability($row); // intentionally set twice to keep track of changes
        $memberData->gitw           = $this->reader->getGitw($row);
        $memberData->tdo            = $this->reader->getTdo($row);
        $memberData->statsReportId  = $this->statsReport->id;
        $memberData->save();

        // TODO: this belongs in post processing
        $wd = $memberData->wd;
        $wbo = $memberData->wbo;
        if ($wd || $wbo) return;

        $tdo = preg_match('/^y$/i', $memberData->tdo) ? 1 : 0;
        if ($tdo > 0) {
            $this->totalTdos += $tdo;
            $this->totalTeamMembersDoingTdo++;
        }
        $this->totalTeamMembers++;
    }

    public function postProcess()
    {
        $centerStats = CenterStats::where('center_id', '=', $this->statsReport->center->id)
                                  ->where('reporting_date', '=', $this->statsReport->reportingDate->toDateString())->first();

        if ($centerStats) {
            $data = CenterStatsData::find($centerStats->actualDataId);
            if ($data) {
                $data->tdo = $this->getTdo();
                $data->save();
            }
        }
    }
}
