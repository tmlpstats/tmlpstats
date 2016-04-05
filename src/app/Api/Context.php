<?php namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use TmlpStats as Models;
use TmlpStats\Http\Controllers\Controller;

/**
 * Context is decisive.
 */
class Context
{
    protected $user = null;
    protected $request = null;
    protected $region = null;
    protected $center = null;
    protected $reportingDate = null;

    protected $dateSelectAction = null;
    protected $dateSelectActionParams = [];

    public function __construct(Guard $auth, Request $request)
    {
        $this->user = $auth->user();
        $this->request = $request;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getCenter($fallback = false)
    {
        $center = $this->center;
        if ($center == null && $fallback) {
            // TODO do this right, don't make it reliant on controller state, invert the paradigm.
            return App::make(Controller::class)->getCenter($this->request);
        }
        return $center;
    }

    public function setCenter(Models\Center $center, $setRegion = true)
    {
        $this->center = $center;
        if ($setRegion) {
            $this->setRegion($center->region);
        }
    }

    public function getGlobalRegion($fallback = false)
    {
        if ($region = $this->getRegion($fallback)) {
            $region = $region->getParentGlobalRegion();
        }
        return $region;
    }

    public function getRegion($fallback = false)
    {
        $region = $this->region;
        if ($region == null && $fallback) {
            // TODO do this right, don't make it reliant on controller state, invert the paradigm.
            $region = App::make(Controller::class)->getRegion($this->request);
        }
        return $region;
    }

    public function setRegion(Models\Region $region)
    {
        $this->region = $region;
    }

    public function getRawSetting($name, $center = null)
    {
        if ($center == null) {
            $center = $this->getCenter();
        }
        $setting = Models\Setting::get($name, $center);
        if ($setting != null) {
            return $setting->value;
        }
    }

    public function getSetting($name, $center = null)
    {
        $value = $this->getRawSetting($name, $center);
        if ($value === 'false') {
            return false;
        } else if ($value === 'true') {
            return true;
        }
        return $value;
    }

    public function setReportingDate($reportingDate)
    {
        $this->reportingDate = $reportingDate;
    }

    public function getReportingDate()
    {
        $reportingDate = $this->reportingDate;
        if (!$reportingDate) {
            $reportingDate = App::make(Controller::class)->getReportingDate($this->request);
        }
        return $reportingDate;
    }

    public function setDateSelectAction($action, $params = [])
    {
        $this->dateSelectAction = $action;
        $this->dateSelectActionParams = $params;
    }

    public function dateSelectAction($date)
    {
        if ($date instanceof Carbon) {
            $date = $date->toDateString();
        }
        if ($this->dateSelectAction) {
            $params = array_merge($this->dateSelectActionParams, ['date' => $date]);
            return action($this->dateSelectAction, $params);
        }
        return null;
    }
}
