<?php
namespace TmlpStats\Api\Parsers;

use Symfony\Component\HttpFoundation\ParameterBag;

class DictInput
{
    /**
     * Run parsers on demand.
     *
     * @param       $data
     * @param array $requiredParams
     *
     * @return array
     */
    public static function parse($shape, $data, $requiredParams = [])
    {
        $output = [];
        foreach ($data as $key => $value) {
            if (!isset($shape[$key])) {
                continue;
            }

            // The parsers expect data as ParameterBag objects
            if (is_array($data)) {
                $data = new ParameterBag($data);
            }

            $required = in_array($key, $requiredParams);

            $parser = Factory::build($shape[$key]['type']);
            $output[$key] = $parser->run($data, $key, $required);
        }

        return $output;
    }
}
