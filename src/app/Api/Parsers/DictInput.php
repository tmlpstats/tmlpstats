<?php
namespace TmlpStats\Api\Parsers;

use Symfony\Component\HttpFoundation\ParameterBag;

class DictInput
{
    /**
     * Run parsers on demand.
     *
     * @param       $shape           List of valid properties and config
     * @param       $data
     * @param array $requiredParams
     *
     * @return array
     */
    public static function parse($shape, $data, $requiredParams = [])
    {
        $output = [];

        // The parsers expect data as ParameterBag objects
        if (is_array($data)) {
            $data = new ParameterBag($data);
        }

        foreach ($shape as $key => $conf) {
            $required = in_array($key, $requiredParams);

            if (!$data->has($key)) {
                if (array_get($conf, 'assignId', false) && $data->has($key . 'Id')) {
                    $data->set($key, $data->get($key . 'Id'));
                } else if (!$required) {
                    continue;
                }
            }

            $parser = Factory::build($shape[$key]['type']);
            $output[$key] = $parser->run($data, $key, $required);
        }

        return $output;
    }
}
