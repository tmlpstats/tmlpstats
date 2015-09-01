<?php
namespace TmlpStats\Import;

abstract class ImportDocument
{
    protected $center = NULL;
    protected $quarter = NULL;
    protected $reportingDate = NULL;
    protected $statsReport = NULL;
    protected $globalReport = NULL;

    public function __get($name)
    {
        switch($name)
        {
            case 'center': return $this->center;
            case 'quarter': return $this->quarter;
            case 'statsReport': return $this->statsReport;
            case 'reportingDate': return $this->reportingDate;
            default: return NULL;
        }
    }

    abstract public function import();

    abstract protected function loadCenter();
    abstract protected function loadQuarter();
    abstract protected function loadDate();

    abstract protected function getValidator();
}
