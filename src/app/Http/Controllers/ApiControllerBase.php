<?php

namespace TmlpStats\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use TmlpStats\Http\Controllers\Controller;
use TmlpStats\StatsReport;

class ApiControllerBase extends Controller
{
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

}
