<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use TmlpStats as Models;
use TmlpStats\Encapsulations\Scopes;
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

    public function clearEncapsulations()
    {
        $this->encapsulations = [];
    }

    /**
     * A convenience shortcut for context->getUser()->can('privilege', $model)
     * @param  string $priv   The privilege to check
     * @param  Object $target The target to check privileges against, usually an eloquent model.
     * @return bool  True if the user has the privilege, False otherwise.
     */
    public function can($priv, $target)
    {
        if (!$this->getUser()) {
            return false;
        }

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
            $this->setRegion($center->region, false);
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
    public function setRegion(Models\Region $region, $setCenter = true)
    {
        if ($this->region && $this->region->id === $region->id) {
            return;
        }

        $this->region = $region;

        if ($setCenter) {
            $centers = $region->centers;
            if ($centers && $this->getGlobalRegion()->id !== $this->user->homeRegion(true)->id) {
                // in foreign regions, use the first center off the list
                $center = $centers[0];
            } else {
                // in our home region or when we don't have another center, use the user's home center
                $center = $this->user->center;
            }

            if ($center) {
                // If this is a report-only user with a region based reportToken, they don't have a parent center
                $this->setCenter($center, false);
            }
        }
    }

    // Each of the settings lookups represents one of the 'scopes' for a setting.
    // The scopes are encapsulations which cache settings for a specific level, and for specific parameters.
    // For example, if two centers in the same region, then resolving a setting for each center
    // would end up resolving the same RegionScope object, and not load the settings for that region twice.
    protected static $settingsLookups = [
        [Scopes\CenterQuarterScope::class, ['center', 'quarter']],
        [Scopes\CenterScope::class, ['center']],
        [Scopes\RegionQuarterScope::class, ['region', 'quarter']], // handles both child regions and global regions
        [Scopes\RegionScope::class, ['region']], // handles both child regions and global regions
        [Scopes\GlobalScope::class, []],
    ];

    public function getRawSetting($name, $regionOrCenter = null, $quarter = null)
    {
        if (!$regionOrCenter) {
            $regionOrCenter = $this->getCenter() ?: $this->getRegion();
        }
        $center = null;
        $region = null;
        if ($regionOrCenter !== null) {
            if ($regionOrCenter instanceof Models\Region) {
                $region = $regionOrCenter;
            } else {
                $center = $regionOrCenter;
                $region = $center->region;
            }
        }

        $inputs = compact('center', 'quarter', 'region');

        // Loop through settings lookups, resolving desired params.
        // If all the desired params exist and have values (not null),
        // then instantiate/fetch the encapsulation with those parameters.
        // The first one which returns a non-null value is used.
        foreach (static::$settingsLookups as $config) {
            list($encapsulationClass, $desiredParams) = $config;
            $outParams = [];
            foreach ($desiredParams as $n) {
                if ($v = $inputs[$n]) {
                    $outParams[$n] = $inputs[$n];
                } else {
                    continue 2; // null value, go to the next lookup
                }
            }
            $encapsulation = $this->getEncapsulation($encapsulationClass, $outParams);
            if (($value = $encapsulation->getScopedSetting($name)) !== null) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Get a setting value, interpreted as JSON as an array.
     * @param  string         $name    The name of the center.
     * @param  mixed          $regionOrCenter  Region or Center, which will default back to the last value of setCenter. Recommended to be set.
     * @param  Models\Quarter $quarter Quarter, which if omitted means get a setting for any quarter.
     * @return Any A JSON value, most likely an array, but could be any other valid root JSON object
     */
    public function getSetting($name, $regionOrCenter = null, $quarter = null)
    {
        $value = $this->getRawSetting($name, $regionOrCenter, $quarter);
        if ($value === null) {
            return null;
        } else {
            $decoded = json_decode($value, true);
            // For now, we treat non-parseable JSON as a string value. This may change once we've transitioned all settings to using getSetting.
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }

            return $value;
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

        $reportingDate = null;
        $reportingDateString = '';

        // First check if the date is already cached in the session
        if (Session::has('viewReportingDate')) {
            $reportingDateString = Session::get('viewReportingDate');
        }

        // If we have a reportToken, use the reportingDate from that report
        if (!$reportingDateString && Session::has('reportTokenId')) {
            $reportToken = Models\ReportToken::find(Session::get('reportTokenId'));

            if ($reportToken) {
                $report = $reportToken->getReport();
                $reportingDateString = $report->reportingDate->toDateString();
                Session::set('viewReportingDate', $reportingDateString);
            }
        }

        // Finally, create date or get reasonable default
        if ($reportingDateString) {
            $reportingDate = Carbon::createFromFormat('Y-m-d', $reportingDateString);
        } else {
            $reportingDate = $this->getSubmissionReportingDate();
        }

        $reportingDate = $reportingDate->startOfDay();

        $this->setReportingDate($reportingDate);

        return $reportingDate;
    }

    public function getSubmissionReportingDate()
    {
        $reportingDate = null;

        switch (Carbon::now()->dayOfWeek) {
            case Carbon::SATURDAY:
                $reportingDate = new Carbon('last friday');
                break;
            case Carbon::SUNDAY:
            case Carbon::MONDAY:
            case Carbon::TUESDAY:
            case Carbon::WEDNESDAY:
            case Carbon::THURSDAY:
                $reportingDate = new Carbon('next friday');
                break;
            case Carbon::FRIDAY:
                $reportingDate = Carbon::now();
                break;
        }

        return $reportingDate->startOfDay();
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

    public static function ensure()
    {
        return App::make(self::class);
    }
}
