<?php

namespace TmlpStats\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Response;
use TmlpStats as Models;
use TmlpStats\Http\Controllers\Controller;

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

        if (!in_array($callable, $this->unauthenticatedMethods) && Auth::user() == null) {
            abort(401, 'You must be authenticated to access the api.');
        }

        $result = $this->$callable($input);
        return Response::json($result);
    }

    // These are used by auto-generated API functions
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

    protected function parse_array($input, $key)
    {
        if ($input->has($key)) {
            $arr = $input->get($key);
            if (is_array($arr)) {
                return $arr;
            } else if (is_string($arr)) {
                return json_decode($arr, true);
            }
        }
        abort(400);
    }

    protected function parse_Center($input, $key)
    {
        if ($input->has($key)) {
            $val = $input->get($key);
            if (is_numeric($val)) {
                $center = Models\Center::findOrFail(intval($val));
            } else {
                $center = Models\Center::abbreviation($val)->firstOrFail();
            }
            return $center;
        }
        abort(400);
    }

    protected function parse_Region($input, $key)
    {
        if ($input->has($key)) {
            $val = $input->get($key);
            if (is_numeric($val)) {
                $region = Models\Region::findOrFail(intval($val));
            } else {
                $region = Models\Region::abbreviation($val)->firstOrFail();
            }
            return $region;
        }
        abort(400);
    }

    protected function parse_LocalReport($input, $key)
    {
        if ($input->has($key)) {

            if (is_numeric($input->get($key))) {
                return Models\StatsReport::findOrFail($input->get($key));
            } else {
                abort(400);
            }
        }
        if ($key == 'localReport') {
            // TODO some fallbacks for the default naming
        }
    }

    protected function parse_GlobalReport($input, $key)
    {
        if ($input->has($key)) {

            if (is_numeric($input->get($key))) {
                return Models\GlobalReport::findOrFail($input->get($key));
            } else {
                abort(400);
            }
        }
        if ($key == 'globalReport') {
            // TODO some fallbacks for the default naming
        }
    }
}
