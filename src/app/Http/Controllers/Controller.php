<?php
namespace TmlpStats\Http\Controllers;

use App;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use TmlpStats\Api;

class Controller extends BaseController
{
    use ValidatesRequests, AuthorizesRequests;

    protected $context;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->context = App::make(Api\Context::class);
    }

    protected function getApi($apiName)
    {
        if (strpos($apiName, '.') !== false) {
            $apiName = str_replace('.', '\\', $apiName);
        }
        return App::make($apiName);
    }
}
