<?php

namespace TmlpStats\GraphQL\Query;

use App;
use Folklore\GraphQL\Support\Query;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use TmlpStats as Models;
use TmlpStats\Api;

class AdminRegionQuery extends Query
{
    protected $attributes = [
        'name' => 'adminRegion',
        'description' => 'A query',
    ];

    public function type()
    {
        return GraphQL::type('AdminRegion');
    }

    public function args()
    {
        return [
            'regionAbbr' => Type::nonNull(Type::string()),
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $info)
    {

        $region = Models\Region::abbreviation($args['regionAbbr'])->firstOrFail();
        App::make(Api\Base\AuthenticatedApiBase::class)->assertCan('viewManageUi', $region);

        return [
            'region' => $region,
            'bleh' => $region,
        ];
    }
}
