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

use App;
use TmlpStats\Api;
use TmlpStats\Http\Controllers\ApiControllerBase;
trait {{ $namespace->id }}ReportDispatch {
    public function newDispatch($action, {!! $fwp !!}) {
        $funcName = $this->dispatchFuncName($action);
        if (!$funcName) {
            // TODO FAIL
        }
        return $this->$funcName({!! $fwp !!});
    }

    public function dispatchFuncName($action) {
        switch ($action) {
@foreach ($namespace->flatReports() as $report)
            case '{{ $report->id }}':
            case '{{ strtolower($report->id) }}':
                return 'get{{ $report->controllerFuncName() }}';
                break;
@endforeach
        }
    }
@foreach ($namespace->flatReports() as $report)
    // Get report {!! $report->name !!}
    protected abstract function get{{ $report->id }}();

@endforeach
}
