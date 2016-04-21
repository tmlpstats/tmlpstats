<?php
namespace TmlpStats\Api\Base;

use Illuminate\Auth\Guard;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use TmlpStats\Api;
use TmlpStats\Api\Parsers;

class ApiBase
{
    /**
     * Array of valid properties and their config.
     *  example:
     *      'firstName' => [
     *          'owner' => 'person', // optionally specify secondary objects that this property belongs to
     *          'type' => 'string',  // property type, used for validation
     *      ],
     *
     * @var array
     */
    protected $validProperties = [];

    public function __construct(Guard $auth, Request $request)
    {
        $this->user = $auth->user();
        $this->request = $request;
    }

    public function parseInputs($data, $requiredParams = [])
    {
        $output = [];
        foreach ($data as $key => $value) {
            if (!isset($this->validProperties[$key])) {
                continue;
            }

            // The parsers expect data as ParameterBag objects
            if (is_array($data)) {
                $data = new ParameterBag($data);
            }

            $required = isset($requiredParams[$key]);

            $parser = Parsers\Factory::build($this->validProperties[$key]['type']);
            $output[$key] = $parser->run($data, $key, $required);
        }
        return $output;
    }
}
