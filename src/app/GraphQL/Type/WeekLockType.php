<?php
namespace TmlpStats\GraphQL\Type;

use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL;
use GraphQL\Type\Definition\Type;

class WeekLockType extends BaseType
{
    // the command "php artisan graphql:codegen" manages
    // the definitions between the special graphql comments following

    // GRAPHQL_GENERATED
    protected $attributes = [
        'name' => 'WeekLock',
        'description' => "",
    ];

    public function fields()
    {
        return [
            'reportingDate' => [
                'type' => Type::nonNull(GraphQL::type('Date')),
            ],
            'locks' => [
                'type' => Type::listOf(GraphQL::type('ScoreboardLock')),
            ],
        ];
    }
    // END_GRAPHQL_GENERATED

}
