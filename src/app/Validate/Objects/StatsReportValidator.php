<?php
namespace TmlpStats\Validate\Objects;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Validate\ValidatorAbstract;

class StatsReportValidator extends ValidatorAbstract
{
    protected $sheetId = ImportDocument::TAB_WEEKLY_STATS;

    protected function validate($data)
    {
        $expectedVersion = isset($data['expectedVersion'])
            ? $data['expectedVersion']
            : null;

        $expectedDate = isset($data['expectedDate'])
            ? $data['expectedDate']
            : null;

        if ($expectedVersion && $expectedVersion != $this->center->sheetVersion) {
            $this->addMessage('IMPORTDOC_SPREADSHEET_VERSION_MISMATCH', $expectedVersion, $this->center->sheetVersion);
            $this->isValid = false;
        }

        if ($expectedDate && $expectedDate->ne($this->statsReport->reportingDate)) {

            if ($this->statsReport->reportingDate->diffInDays($this->statsReport->quarter->endWeekendDate) < 7) {
                // Reporting in the last week of quarter
                if ($this->statsReport->reportingDate->ne($this->statsReport->quarter->endWeekendDate)) {
                    $this->addMessage('IMPORTDOC_SPREADSHEET_DATE_MISMATCH_LAST_WEEK', $this->statsReport->reportingDate->toDateString(), $this->statsReport->quarter->endWeekendDate->toDateString());
                    $this->isValid = false;
                }
            } else {
                $this->addMessage('IMPORTDOC_SPREADSHEET_DATE_MISMATCH', $this->statsReport->reportingDate->toDateString(), $expectedDate->toDateString());
                $this->isValid = false;
            }
        }

        return $this->isValid;
    }
}
