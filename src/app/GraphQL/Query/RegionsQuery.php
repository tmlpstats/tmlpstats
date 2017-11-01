<?php

namespace TmlpStats\GraphQL\Query;

use Folklore\GraphQL\Support\Query;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

class RegionsQuery extends Query
{
    protected $attributes = [
        'name' => 'regions',
        'description' => 'A query',
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('Region'));
    }

    public function args()
    {
        return [

        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        return [];
    }
}
