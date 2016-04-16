<?php

namespace TmlpStats\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Response;
use TmlpStats as Models;
use TmlpStats\Http\Controllers\Controller;
use TmlpStats\Api\Exceptions as ApiExceptions;
use TmlpStats\Api\Parsers;

class ApiControllerBase extends Controller
{
    /**
     * List of any method that does not require authentication.
     *
     * This is populated by the codegen if method has access: any
     *
     * @var array
     */
    protected $unauthenticatedMethods = [];

    public function __construct()
    {
        // Disable default middleware
    }

    /**
     * Handle an API call.
     *
     * @return \Illuminate\Http\Response
     */
    public function apiCall(Request $request)
    {
        $input = $request->json();
        $method = $input->get('method');
        if (!isset($this->methods[$method])) {
            throw new ApiExceptions\NotAllowedException('API method not allowed.');
        }
        $callable = $this->methods[$method];

        if (!in_array($callable, $this->unauthenticatedMethods) && Auth::user() == null) {
            throw new ApiExceptions\NotAuthenticatedException('You must be authenticated to access the api.');
        }

        $result = $this->$callable($input);
        return Response::json($result);
    }

    /**
     * Parse the input parameter
     *
     * @param  array $input  Input array
     * @param  string $key   Parameter key inside of input array
     * @param  string $type  Parameter type. Used to choose parser
     * @return mixed         Parsed parameter
     */
    protected function parse($input, $key, $type)
    {
        switch ($type) {
            case 'int':
                return Parsers\IntParser::create()->run($input, $key);
            case 'string':
                return Parsers\StringParser::create()->run($input, $key);
            case 'bool':
                return Parsers\BoolParser::create()->run($input, $key);
            case 'array':
                return Parsers\ArrayParser::create()->run($input, $key);
            case 'Center':
                return Parsers\CenterParser::create()->run($input, $key);
            case 'Region':
                return Parsers\RegionParser::create()->run($input, $key);
            case 'LocalReport':
                return Parsers\LocalReportParser::create()->run($input, $key);
            case 'GlobalReport':
                return Parsers\GlobalReportParser::create()->run($input, $key);
            default:
                throw new ApiExceptions\UnknownException("Unknown parameter type {$type}");
        }
    }
}
