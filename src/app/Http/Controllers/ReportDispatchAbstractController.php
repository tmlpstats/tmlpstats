<?php
namespace TmlpStats\Http\Controllers;

use Cache;
use Illuminate\Http\Request;
use Illuminate\View\View;
use TmlpStats\Util;

abstract class ReportDispatchAbstractController extends Controller
{
    const CACHE_TTL = 60;
    const USE_CACHE = true;

    /**
     * List of reports that will not be cached
     *
     * @var array
     */
    protected $dontCache = [];

    /**
     * Returns an instance of the appropriate Model class by id
     *
     * MUST NOT return null.
     * MUST return a model or abort.
     *
     * @param $id
     * @return mixed
     */
    abstract public function getById($id);

    /**
     * Runs the appropriate logic and returns resulting view
     *
     * @param Request $request
     * @param $model
     * @param $report
     * @return \Illuminate\View\View
     */
    abstract public function runDispatcher(Request $request, $model, $report);

    /**
     * Get the cache key to use for given model/report combo
     *
     * @param $model
     * @param $report
     * @return string
     */
    public function getCacheKey($model, $report)
    {
        $keyBase = lcfirst(Util::getClassBasename($model));

        return "{$keyBase}{$model->id}:{$report}";
    }

    /**
     * Get the tags to use when caching response.
     *
     * Adding tags allows you to flush a group of responses when needed
     *
     * @param $model
     * @param $report
     * @return array
     */
    public function getCacheTags($model, $report)
    {
        return ['reports'];
    }

    /**
     * Entry method to dispatch a report
     *
     * Caches reponses based on response from useCache()
     *
     * @param Request $request
     * @param $id
     * @param $report
     * @return View|string|null
     */
    public function dispatchReport(Request $request, $id, $report)
    {
        $model = $this->getById($id);

        $this->authorizeReport($model, $report);

        Util::setReportDate($model->reportingDate);

        $response = null;

        if ($this->useCache($report)) {
            $cacheKey = $this->getCacheKey($model, $report);
            $tags = $this->getCacheTags($model, $report);

            $response = Cache::tags($tags)->get($cacheKey);
        }

        if (!$response) {
            $response = $this->runDispatcher($request, $model, $report);
        }

        if ($this->useCache($report) && $response) {
            if ($response instanceof View) {
                $renderedResponse = $response->render();
            } else {
                $renderedResponse = $response;
            }
            Cache::tags($tags)->put($cacheKey, $renderedResponse, static::CACHE_TTL);
        }

        if (!$response) {
            abort(404);
        }

        return $response;
    }

    /**
     * Authorize the report
     *
     * This has a sensible default. Allows additional authorization per report as needed.
     *
     * @param $model
     * @param $report
     * @return \Illuminate\Auth\Access\Response
     */
    public function authorizeReport($model, $report)
    {
        return $this->authorize('read', $model);
    }

    /**
     * Get setting for whether or not to use cached data
     *
     * @return bool
     */
    public function useCache($report)
    {
        return env('REPORTS_USE_CACHE', static::USE_CACHE) && !in_array($report, $this->dontCache);
    }
}
