<?php
namespace TmlpStats\Api\Traits;

use Illuminate\Support\Facades\Storage;
use TmlpStats as Models;

trait CachesOutput
{
    protected function getCacheDir(Models\GlobalReport $report, Models\Region $region, $endpoint)
    {
        $class = ucfirst(class_basename($this));
        return "cache/{$report->reportingDate->toDateString()}/{$class}/{$region->abbreviation}/{$endpoint}.json";
    }

    public function clearCache(Models\GlobalReport $report, Models\Region $region)
    {
        // clear cache
        $class = ucfirst(class_basename($this));
        Cache::tags(["{$class}{$report->id}"])->flush();

        // rm saved report files
        $dir = $this->getCacheDir($report, $region);
        foreach(glob("{$dir}/*") as $file) {
            if(is_file($file)) {
                unlink($file);
            }
        }
    }

    protected function getFromCache(Models\GlobalReport $report, Models\Region $region, $endpoint)
    {
        $cacheKey = $this->getCacheDir($report, $region, $endpoint);
        if (!Storage::exists($cacheKey)) {
            return null;
        }

        if ($contents = Storage::get($cacheKey)) {
            return json_decode($contents, true);
        }

        return null;
    }

    protected function putInCache(Models\GlobalReport $report, Models\Region $region, $endpoint, $data)
    {
        $cacheKey = $this->getCacheDir($report, $region, $endpoint);
        Storage::put($cacheKey, json_encode($data));
    }
}
