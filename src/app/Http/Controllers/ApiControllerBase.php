<?php

namespace TmlpStats\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Response;
use Symfony\Component\HttpFoundation\ParameterBag;
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
    public function apiCall(Request $request, $method = '')
    {
        $input = new ParameterBag($request->all());
        if (!$method) {
            $method = $input->get('method');
        }

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
     * @param  array  $input     Input array
     * @param  string $key       Parameter key inside of input array
     * @param  string $type      Parameter type. Used to choose parser
     * @param  bool   $required  Is input parameter required?
     *
     * @return mixed         Parsed parameter
     */
    protected function parse($input, $key, $type, $required = true)
    {
        $parser = Parsers\Factory::build($type);

        return $parser->run($input, $key, $required);
    }
}
