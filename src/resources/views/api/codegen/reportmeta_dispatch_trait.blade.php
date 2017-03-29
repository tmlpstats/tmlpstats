<{!! "?php" !!}
<?php
$fwp = implode(', ', $namespace->forwardParams);
?>
namespace TmlpStats\Http\Controllers\Traits;

///////////////////////////////
// THIS CODE IS AUTO-GENERATED
// do not edit this code by hand!
//
// To edit the resulting API code, instead edit config/reports.yml
// and then run the command:
//   php artisan reports:codegen
//
///////////////////////////////

trait {{ $namespace->id }}ReportDispatch
{
    // NOTE these are lowercased for now to allow case insensitivity, may change soon.
    protected $dispatchMap = [
@foreach ($namespace->flatReports() as $report)
        '{{ strtolower($report->id) }}' => [
            'id' => '{{ $report->id }}',
            'method' => '{{ $report->controllerFuncName() }}',
@if ($report->cacheTime !== 'default')
            'cacheTime' => @json($report->cacheTime),
@endif
        ],
@endforeach
    ];

    public function getPageCacheTime($report)
    {
        $globalUseCache = env('REPORTS_USE_CACHE', true);
        if (!$globalUseCache) {
            return 0;
        }
        $config = array_get($this->dispatchMap, strtolower($report), []);
        $cacheTime = array_get($config, 'cacheTime', 60);

        return $cacheTime;
    }

    public function newDispatch($report, {!! $fwp !!})
    {
        $config = array_get($this->dispatchMap, strtolower($report), null);
        if (!$config) {
            throw new \Exception("Could not find report $report");
        }
        $funcName = $config['method'];

        return $this->$funcName({!! $fwp !!});
    }

@foreach ($namespace->flatReports() as $report)
    // Get report {!! $report->name !!}
    protected abstract function {{ $report->controllerFuncName() }}();

@endforeach
}
