<{!! "?php" !!}
namespace TmlpStats\Http\Controllers;

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

class ApiController extends ApiControllerBase
{
    protected $methods = [
@foreach ($apiMethodsFlat as $method)
        "{{ $method->absName }}" => "{{ $method->absNameLocal() }}",
@endforeach
    ];

    protected $tokenAuthenticatedMethods = [
@foreach ($apiMethodsFlat as $method)
@if ($method->access === 'token')
        "{{ $method->absNameLocal() }}",
@endif
@endforeach
    ];

    protected $unauthenticatedMethods = [
@foreach ($apiMethodsFlat as $method)
@if ($method->access === 'any')
        "{{ $method->absNameLocal() }}",
@endif
@endforeach
    ];

@foreach ($apiMethodsFlat as $method)
    protected function {{ $method->absNameLocal() }}($input)
    {
        return App::make(Api\{{ $method->packageName() }}::class)->{{ $method->name }}(
@foreach ($method->params as $param)
            $this->parse($input, '{{ $param->name }}', '{{ $param->type }}'<?php if (!$param->required) {?>, false<?php } ?>)<?php if (!$param->isLast) { ?>,<?php } ?>

@endforeach
        );
    }
@endforeach
}
