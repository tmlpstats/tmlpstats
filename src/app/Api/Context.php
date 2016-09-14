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

    // Encapsulation parameters
    protected $encapsulationParams = [];
    protected $encapsulations = [];

    // Encapsulation classes

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
        $this->encapsulationParams['region'] = $region;
    }

    public function getRawSetting($name, $center = null, $quarter = null)
    {
        if ($center == null) {
            $center = $this->getCenter();
        }
        if ($quarter == null) {
            $setting = Models\Setting::get($name, $center);
        } else {
            $setting = Models\Setting::get($name, $center, $quarter);
        }
        if ($setting != null) {
            return $setting->value;
        }

        return null;
    }

    public function getSetting($name, $center = null, $quarter = null)
    {
        $value = $this->getRawSetting($name, $center, $quarter);
        if ($value === null) {
            return null;
        } else if ($value === 'false') {
            return false;
        } else if ($value === 'true') {
            return true;
        } else {
            return json_decode($value, true);
        }
    }

    public function setReportingDate($reportingDate)
    {
        $this->reportingDate = $reportingDate;
        $this->encapsulationParams['reportingDate'] = $reportingDate;
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
        // Handy way to get the existing reporting date
        if ($date == 'RD') {
            $date = $this->getReportingDate();
        }

        if ($date instanceof Carbon) {
            $date = $date->toDateString();
        }
        if ($this->dateSelectAction) {
            $params = array_merge($this->dateSelectActionParams, ['date' => $date]);

            return action($this->dateSelectAction, $params);
        }

        return null;
    }

    public function can($priv, $target)
    {
        return $this->getUser()->can($priv, $target);
    }

    /// Encapsulation is a potentially temporary fix for while we're reorganizing data.

    public function getEncapsulation($className, $ctx)
    {
        $eKey = '';
        ksort($ctx);
        foreach ($ctx as $k => $v) {
            $eKey .= "{$k}={$v->id}";
        }
        $eKey .= "::${className}";
        if (($obj = array_get($this->encapsulations, $eKey, null)) == null) {
            $obj = $this->encapsulations[$eKey] = App::make($className, $ctx);
        }

        return $obj;
    }
}
