<?php
namespace TmlpStats\Import\Xlsx\DataImporter\V11;

use TmlpStats\Quarter;
use TmlpStats\TeamMember;
use TmlpStats\TeamMemberData;
use TmlpStats\CenterStats;
use TmlpStats\CenterStatsData;

use Carbon\Carbon;

class ClassListImporter extends DataImporterAbstract
{
    protected $classDisplayName = "Class List";

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
        self::$blockT1Q1[] = $this->excelRange('A','S');
        self::$blockT1Q1[] = $this->excelRange(195,224);

        self::$blockT1Q2[] = $this->excelRange('A','S');
        self::$blockT1Q2[] = $this->excelRange(139,168);

        self::$blockT1Q3[] = $this->excelRange('A','S');
        self::$blockT1Q3[] = $this->excelRange(83,112);

        self::$blockT1Q4[] = $this->excelRange('A','S');
        self::$blockT1Q4[] = $this->excelRange(27,56);

        self::$blockT2Q1[] = $this->excelRange('A','S');
        self::$blockT2Q1[] = $this->excelRange(227,246);

        self::$blockT2Q2[] = $this->excelRange('A','S');
        self::$blockT2Q2[] = $this->excelRange(171,190);

        self::$blockT2Q3[] = $this->excelRange('A','S');
        self::$blockT2Q3[] = $this->excelRange(115,134);

        self::$blockT2Q4[] = $this->excelRange('A','S');
        self::$blockT2Q4[] = $this->excelRange(59,78);
    }

    public function getTdo()
    {
        $tdoActual = '0%';
        if ($this->totalTeamMembers > 0) {
            $tdoActual = round(($this->totalTeamMembersDoingTdo/$this->totalTeamMembers)*100).'%';
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
        $completionQuarter = Quarter::findByDate($args[1]);

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
        $memberData->reasonWithdraw = $this->reader->getReasonWithdraw($row);
        $memberData->travel         = $this->reader->getTravel($row);
        $memberData->room           = $this->reader->getRoom($row);
        $memberData->comment        = $this->reader->getComment($row);
        $memberData->accountability = $this->reader->getAccountability($row); // intentionally set twice to keep track of changes
        $memberData->gitw           = $this->reader->getGitw($row);
        $memberData->tdo            = $this->reader->getTdo($row);
        $memberData->additionalTdo  = $this->reader->getAdditionalTdo($row);
        $memberData->statsReportId  = $this->statsReport->id;
        $memberData->save();

        $wd = $memberData->wd;
        $wbo = $memberData->wbo;
        if ($wd || $wbo) return;

        $tdo = (int)$memberData->tdo;
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
