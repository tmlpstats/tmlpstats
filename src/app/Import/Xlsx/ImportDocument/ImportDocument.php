<?php
namespace TmlpStats\Import\Xlsx\ImportDocument;

use TmlpStats\CenterStatsData;
use TmlpStats\GlobalReport;
use TmlpStats\Quarter;
use TmlpStats\Center;
use TmlpStats\StatsReport;
use TmlpStats\Message;
use TmlpStats\Util;
use TmlpStats\TmlpRegistration;

use TmlpStats\Import\Xlsx\DataImporter\DataImporterFactory;
use Carbon\Carbon;

use Auth;
use TmlpStats\Validate\ValidationManager;

class ImportDocument extends ImportDocumentAbstract
{
    protected $importers = array();

    // TODO: some of this validation logic belongs in the Validator
    protected function validateReport()
    {
        $data = [];
        foreach ($this->importers as $type => $importer) {
            $data[$type] = $importer->getData();
        }
        $data['expectedVersion'] = $this->enforceVersion ? $this->version : null;
        $data['expectedDate'] = $this->expectedDate;

        $validationManager = new ValidationManager($this->statsReport);
        $isValid = $validationManager->run($data);

        unset($data['expectedVersion']);
        unset($data['expectedDate']);

        foreach ($data as $type => $importData) {
            $this->importers[$type]->setData($importData);
        }

        $this->mergeMessages($validationManager->getMessages());

        return $isValid;
    }

    protected function process()
    {
        $this->loadCenter();
        $this->loadDate();
        $this->loadQuarter();

        if (!$this->isValid()) {
            // Stop processing. We can't find center or reporting date.
            return;
        }

        $this->statsReport = StatsReport::firstOrCreate(array(
            'center_id'           => $this->center->id,
            'quarter_id'          => $this->quarter->id,
            'reporting_date'      => $this->reportingDate->toDateString(),
            'submitted_at'        => null,
        ));
        $this->statsReport->userId = Auth::user()->id;
        $this->statsReport->save();

        // Order matters here. TmlpRegistrations and ContactInfo search for team members
        // so ClassList must be loaded first
        $this->processWeeklyStats();
        $this->processClassList();
        $this->processCourseInfo();
        $this->processContactInfo();
        $this->processTmlpRegistrations();
    }

    protected function loadCenter()
    {
        $data = $this->getWeeklyStatsSheet();

        $centerName = $data[1]['G'];

        $this->center = Center::name($centerName)->first();
        if (!$this->center) {
            $centerListObjects = Center::byRegion(Auth::user()->homeRegion())->orderBy('name')->get();
            $centerList = array();

            foreach ($centerListObjects as $center) {
                $centerList[] = $center->name;
            }

            $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_CENTER_NOT_FOUND', $centerName, implode(', ', $centerList));
        } else if (!$this->center->active) {
            $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_CENTER_INACTIVE', $centerName);
        }
    }

    protected function loadDate()
    {
        $data = $this->getWeeklyStatsSheet();

        $reportingDate = $data[2]['A'];

        $this->reportingDate = Util::getExcelDate($reportingDate);

        if (!$this->reportingDate) {
            // Parse international dates properly
            $this->reportingDate = Util::parseUnknownDateFormat($reportingDate);
            $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_DATE_FORMAT_INCORRECT', $reportingDate);
        }

        if (!$this->reportingDate || $this->reportingDate->lt(Carbon::create(1980,1,1,0,0,0))) {
            $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_DATE_NOT_FOUND', $reportingDate);
        }

        if ($this->reportingDate &&  $this->reportingDate->dayOfWeek != Carbon::FRIDAY) {
            $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_DATE_NOT_FRIDAY', $this->reportingDate->toDateString());
            $this->reportingDate = null;
        }

        if ($this->reportingDate) {
            Util::setReportDate($this->reportingDate);
        }
    }

    protected function loadVersion()
    {
        if ($this->version === null) {

            $data = $this->getWeeklyStatsSheet();

            $version = $data[2]['L'];

            if (!preg_match("/^V((\d+\.\d+)(\.\d+)?[a-z]?)$/i", $version, $matches)) {
                $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_VERSION_FORMAT_INCORRECT', $version);
            } else {
                $this->version = $matches[1]; // only grab to num
            }
        }
    }

    protected function loadQuarter()
    {
        if (!$this->center || !$this->reportingDate) {
            // Don't try to load the quarter without a center.
            // No need to throw error, one has already been logged
            return;
        }
        $this->quarter = Quarter::getQuarterByDate($this->reportingDate, $this->center->region);
        if (!$this->quarter) {
            $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_QUARTER_NOT_FOUND', $this->reportingDate->toDateString());
        }
    }

    protected function processWeeklyStats()
    {
        $sheet = $this->getWeeklyStatsSheet();
        $importer = $this->getCenterStatsImporter($sheet);
        $importer->import();
        $this->mergeMessages($importer->getMessages());

        $this->importers['centerStats'] = $importer;
    }

    protected function processTmlpRegistrations()
    {
        $sheet = $this->getWeeklyStatsSheet();
        $importer = $this->getTmlpRegistrationImporter($sheet);
        $importer->import();
        $this->mergeMessages($importer->getMessages());

        $this->importers['tmlpRegistration'] = $importer;
    }

    protected function processClassList()
    {
        $sheet = $this->getClassListSheet();
        $importer = $this->getClassListImporter($sheet);
        $importer->import();
        $this->mergeMessages($importer->getMessages());

        $this->importers['classList'] = $importer;
    }

    protected function processContactInfo()
    {
        $sheet = $this->getContactInfoSheet();
        $importer = $this->getContactInfoImporter($sheet);
        $importer->import();
        $this->mergeMessages($importer->getMessages());

        $this->importers['contactInfo'] = $importer;
    }

    protected function processCourseInfo()
    {
        $sheet = $this->getCourseInfoSheet();
        $importer = $this->getCommCourseInfoImporter($sheet);
        $importer->import();
        $this->mergeMessages($importer->getMessages());

        $this->importers['commCourseInfo'] = $importer;

        $importer = $this->getTmlpGameInfoImporter($sheet);
        $importer->import();
        $this->mergeMessages($importer->getMessages());

        $this->importers['tmlpCourseInfo'] = $importer;
    }

    protected function addMessage($tab, $messageId)
    {
        $message = Message::create($tab);

        $arguments = array_slice(func_get_args(), 2);
        array_unshift($arguments, $messageId, null);

        $message = $this->callMessageAdd($message, $arguments);

        $this->mergeMessages(array($message));
    }

    protected function mergeMessages($messages)
    {
        foreach ($messages as $message) {

            if ($message['type'] == 'error') {
                $this->messages['errors'][] = $message;
            } else {
                $this->messages['warnings'][] = $message;
            }
        }
    }

    // This method is called after validateReport, and only if it passes.
    // Put work here that add/updates data based on valid values
    protected function postProcess()
    {
        // Don't save anything if report is locked
        if ($this->statsReport->locked) {
            $this->addMessage(static::TAB_WEEKLY_STATS, 'IMPORTDOC_STATS_REPORT_LOCKED', $this->center->name, $this->reportingDate->format("M d, Y"));
            return false;
        }

        Util::setReportDate($this->reportingDate);

        if ($this->statsReport->validated) {
            foreach ($this->importers as $name => $importer) {

                $importer->postProcess();

                // Update the Stats Report after post processing
                switch ($name) {
                    case 'contactInfo':
                        $reportingStatistician = $importer->getReportingStatistician();

                        $actual = CenterStatsData::actual()
                            ->byStatsReport($this->statsReport)
                            ->reportingDate($this->statsReport->reportingDate)
                            ->first();
                        if ($actual) {
                            $actual->programManagerAttendingWeekend = $importer->getProgramManagerAttendingWeekend();
                            $actual->classroomLeaderAttendingWeekend = $importer->getClassroomLeaderAttendingWeekend();
                            $actual->save();
                        }

                        $this->statsReport->reportingStatisticianId = $reportingStatistician ? $reportingStatistician->id : null;
                        break;

                    default:
                        break;
                }
            }
            $this->importers = null;
        }

        $this->statsReport->version = $this->version;
        $this->statsReport->submittedAt = $this->submittedAt ?: Carbon::now();
        $this->statsReport->save();

        $this->globalReport = GlobalReport::firstOrCreate([
            'reporting_date' => $this->reportingDate,
        ]);
        $this->globalReport->addCenterReport($this->statsReport);

        $this->saved = true;

        return true;
    }

    protected function getWeeklyStatsSheet()
    {
        return $this->loadSheet(0);
    }
    protected function getClassListSheet()
    {
        return $this->loadSheet(1);
    }
    protected function getCourseInfoSheet()
    {
        return $this->loadSheet(2);
    }
    protected function getContactInfoSheet()
    {
        return $this->loadSheet(3);
    }

    protected function getCenterStatsImporter(&$sheet)
    {
        return DataImporterFactory::build('CenterStats', $sheet, $this->statsReport);
    }
    protected function getTmlpRegistrationImporter(&$sheet)
    {
        return DataImporterFactory::build('TmlpRegistration', $sheet, $this->statsReport);
    }
    protected function getClassListImporter(&$sheet)
    {
        return DataImporterFactory::build('ClassList', $sheet, $this->statsReport);
    }
    protected function getContactInfoImporter(&$sheet)
    {
        return DataImporterFactory::build('ContactInfo', $sheet, $this->statsReport);
    }
    protected function getCommCourseInfoImporter(&$sheet)
    {
        return DataImporterFactory::build('CommCourseInfo', $sheet, $this->statsReport);
    }
    protected function getTmlpGameInfoImporter(&$sheet)
    {
        return DataImporterFactory::build('TmlpGameInfo', $sheet, $this->statsReport);
    }

    // @codeCoverageIgnoreStart
    protected function callMessageAdd($message, $arguments)
    {
        return call_user_func_array(array($message, 'addMessage'), $arguments);
    }
    // @codeCoverageIgnoreEnd
}
