<?php
namespace TmlpStats\GraphQL\Type;

use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL;
use GraphQL\Type\Definition\Type;

class ScoreboardLockType extends BaseType
{
    // the command "php artisan graphql:codegen" manages
    // the definitions between the special graphql comments following

    // GRAPHQL_GENERATED
    protected $attributes = [
        'name' => 'ScoreboardLock',
        'description' => "",
    ];

    public function fields()
    {
        return [
            'week' => [
                'type' => Type::nonNull(GraphQL::type('Date')),
            ],
            'editPromise' => [
                'type' => Type::nonNull(Type::boolean()),
            ],
        ];
    }
    // END_GRAPHQL_GENERATED

}
