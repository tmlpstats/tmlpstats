<?php
namespace TmlpStats\GraphQL\Type;

use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL;
use GraphQL\Type\Definition\Type;

class RegionType extends BaseType
{
    // the command "php artisan graphql:codegen" manages
    // the definitions between the special graphql comments following

    // GRAPHQL_GENERATED
    protected $attributes = [
        'name' => 'Region',
        'description' => "",
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
            ],
            'abbr' => [
                'type' => Type::nonNull(Type::string()),
            ],
        ];
    }
    // END_GRAPHQL_GENERATED

    public function resolveAbbrField($x)
    {
        return $x->abbrLower();
    }

}
