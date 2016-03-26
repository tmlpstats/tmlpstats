<?php

namespace TmlpStats\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use TmlpStats\Center;
use TmlpStats\Http\Controllers\Controller;
use TmlpStats\StatsReport;

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
            // TODO error scenario, return not found API
            abort(400, 'API method not found');
        }
        $callable = $this->methods[$method];

        if (!in_array($callable, $this->unauthenticatedMethods) && !Auth::guard('auth')->login($this->user)) {
            abort(401, 'You must be authenticated to access the api.');
        }

        $result = $this->$callable($input);
        return Response::json($result);
    }

    // These are used by auto-generated API functions
    protected function parse_LocalReport($input, $key)
    {
        if ($input->has($key)) {

            if (is_numeric($input->get($key))) {
                return StatsReport::findOrFail($input->get($key));
            } else {
                abort(400);
            }
        }
        if ($key == 'localReport') {
            // TODO some fallbacks for the default naming
        }
    }

    protected function parse_bool($input, $key)
    {
        if (!$input->has($key)) {
            // missing value defaults to false
            return false;
        }
        if ($input[$key] === false || $input[$key] == 'false' || $input[$key] == 'no') {
            return false;
        }
        return true;
    }

    protected function parse_string($input, $key)
    {
        return (string) $input->get($key);
    }

    protected function parse_int($input, $key)
    {
        return intval($input->get($key));
    }

    protected function parse_Center($input, $key)
    {
        if ($input->has($key)) {
            $val = $input->get($key);
            if (is_numeric($val)) {
                $center = Center::findOrFail(intval($val));
            } else {
                $center = Center::abbreviation($val)->firstOrFail();
            }
            return $center;
        }
        abort(400);
    }

}
