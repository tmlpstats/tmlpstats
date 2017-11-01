<?php
namespace TmlpStats\GraphQL\Type;

use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL;
use GraphQL\Type\Definition\Type;

class AdminRegionQuarterType extends BaseType
{
    // the command "php artisan graphql:codegen" manages
    // the definitions between the special graphql comments following

    // GRAPHQL_GENERATED
    protected $attributes = [
        'name' => 'AdminRegionQuarter',
        'description' => "",
    ];

    public function fields()
    {
        return [
            'rq' => [
                'type' => GraphQL::type('RegionQuarter'),
            ],
            'locks' => [
                'type' => Type::listOf(GraphQL::type('WeekLock')),
            ],
        ];
    }
    // END_GRAPHQL_GENERATED

}
