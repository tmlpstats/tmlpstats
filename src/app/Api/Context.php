<?php namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use TmlpStats as Models;
use TmlpStats\Http\Controllers\Controller;

/**
 * Context is decisive.
 *
 * Context is a singleton object designed to be managed by the Laravel container, which
 * maintains information meant to be of use to shuttle data around and maintain data related to
 * a single invocation / request.
 *
 * The primary reason Context is centralized is that as a simple class it is easy to mock/inject
 * into the app container, as such it frees up a lot of common cases of unit test dependency injection issues.
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

    // Cached encapsulations
    protected $encapsulations = [];

    public function __construct(Guard $auth, Request $request)
    {
        $this->user = $auth->user();
        $this->request = $request;
    }

    /**
     * Get the current user.
     * @return Models\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * A convenience shortcut for context->getUser()->can('privilege', $model)
     * @param  string $priv   The privilege to check
     * @param  Object $target The target to check privileges against, usually an eloquent model.
     * @return bool  True if the user has the privilege, False otherwise.
     */
    public function can($priv, $target)
    {
        return $this->getUser()->can($priv, $target);
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

    /**
     * Set the center on this context.
     * @param Models\Center $center    Center to set.
     * @param boolean       $setRegion if true (the default) setRegion to this center's region.
     */
    public function setCenter(Models\Center $center, $setRegion = true)
    {
        $this->center = $center;
        if ($setRegion) {
            $this->setRegion($center->region);
        }
    }

    /**
     * Get the parent global region (if the current region is not one.)
     * @param  boolean $fallback If true, fallback to the legacy controller method of getting a region.
     * @return Models\Region
     */
    public function getGlobalRegion($fallback = false)
    {
        if ($region = $this->getRegion($fallback)) {
            $region = $region->getParentGlobalRegion();
        }

        return $region;
    }

    /**
     * Get the current region of this context.
     * @param  boolean $fallback If true, fallback to the legacy controller method of getting a region from session.
     * @return Models\Region
     */
    public function getRegion($fallback = false)
    {
        $region = $this->region;
        if ($region == null && $fallback) {
            // TODO do this right, don't make it reliant on controller state, invert the paradigm.
            $region = App::make(Controller::class)->getRegion($this->request);
        }

        return $region;
    }

    /**
     * Set the region locally on this context.
     * It is recommended any and all reporting views either do setCenter or setRegion so that
     * various links based on context can be built correctly.
     *
     * @param Models\Region $region The region.
     */
    public function setRegion(Models\Region $region)
    {
        $this->region = $region;
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

    /**
     * Get a setting value, interpreted as JSON as an array.
     * @param  string          $name    The name of the center.
     * @param  Models\Center   $center  Center, which will default back to the last value of setCenter. Recommended to be set.
     * @param  Models\Quarter  $quarter Quarter, which if omitted means get a setting for any quarter.
     * @return Any A JSON value, most likely an array, but could be any other valid root JSON object
     */
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

    /**
     * Set the reporting date locally on this context. Does not set the session-based date, just the in-memory contextual one.
     */
    public function setReportingDate($reportingDate)
    {
        $this->reportingDate = $reportingDate;
    }

    /**
     * Get the reporting date, falling back to the session-based date (deprecated, but still exists) if no other options.
     * @return Carbon The reporting date
     */
    public function getReportingDate()
    {
        if ($this->reportingDate) {
            return $this->reportingDate;
        }

        return App::make(Controller::class)->getReportingDate();
    }

    /**
     * Set the action to be routed to on date select.
     * Used in views that have a reportingDate route parameter to allow the date select dropdown to work.
     * @param string $action A route action, such as  "FooController@action"
     * @param array  $params An associative array of route parameters to this route, other than "reportingDate"
     */
    public function setDateSelectAction($action, $params = [])
    {
        $this->dateSelectAction = $action;
        $this->dateSelectActionParams = $params;
    }

    /**
     * Resolve a date select link. Requires setDateSelectAction to be called first.
     * @param  string|Carbon $date  The date in question. Can be either a Carbon or an ISO8601 date string.
     * @return string  The URL resolved.
     */
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

    /**
     * Get or create an encapsulation based on a class name and parameters.
     *
     * An encapsulation is an object which is indexed as a function of its input parameters,
     * where all input parameters are things with a `->id` attribute (usually Eloquent models).
     *
     * For a given set of inputs, if an encapsulation exists, that object is returned. Otherwise,
     * an instance of $className is constructed with the provided parameters (which should be type-hinted)
     * with assistance from the service container to provide additional type-hinted values.
     *
     * @param  string $className The class we want to encapsulate. Usually passed via using Blah::class for consistency.
     * @param  array  $ctx       An associative array of parameters to the encapsulation.
     * @return Any    Returns an instance of $className that has been constructed with the ctx parameters.
     */
    public function getEncapsulation($className, $ctx)
    {
        ksort($ctx);
        $eKey = "${className}";
        foreach ($ctx as $k => $v) {
            if ($v instanceof Carbon) {
                $eKey .= ":{$k}={$v->toDateString()}";
            } else {
                $eKey .= ":{$k}={$v->id}";
            }
        }

        if (($obj = array_get($this->encapsulations, $eKey, null)) == null) {
            $obj = $this->encapsulations[$eKey] = App::make($className, $ctx);
        }

        return $obj;
    }
}
