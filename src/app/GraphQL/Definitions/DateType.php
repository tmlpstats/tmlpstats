<?php
namespace TmlpStats\GraphQL\Definitions;

use Carbon\Carbon;
use Folklore\GraphQL\Support\Contracts\TypeConvertible;
use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;

class DateType extends ScalarType implements TypeConvertible
{
    public $name = 'Date';

    /**
     * Serializes an internal value to include in a response.
     *
     * @param mixed $value
     * @return string
     */
    public function serialize($value)
    {
        if ($value instanceof Carbon) {
            return $value->toDateString();
        }

        return $value;
    }

    /**
     * Parses an externally provided value (query variable) to use as an input
     *
     * @param mixed $value
     * @return mixed
     */
    public function parseValue($value)
    {
        if ($value != null && !($value instanceof Carbon)) {
            return Carbon::parse($value)->startOfDay();
        }

        return $value;
    }

    /**
     * Parses an externally provided literal value (hardcoded in GraphQL query) to use as an input.
     *
     * E.g.
     * {
     *   app(regDate: "user@example.com")
     * }
     *
     * @param \GraphQL\Language\AST\Node $valueNode
     * @return string
     * @throws Error
     */
    public function parseLiteral($valueNode)
    {
        // Note: throwing GraphQL\Error\Error vs \UnexpectedValueException to benefit from GraphQL
        // error location in query:
        if (!$valueNode instanceof StringValueNode) {
            throw new Error('Query error: Can only parse strings got: ' . $valueNode->kind, [$valueNode]);
        }

        return Carbon::parse($valueNode->value)->startOfDay();
    }

    public function toType()
    {
        return new static();
    }
}
