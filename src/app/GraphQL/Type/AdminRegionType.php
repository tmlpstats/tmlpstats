<?php
namespace TmlpStats\GraphQL\Type;

use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use TmlpStats as Models;
use TmlpStats\Encapsulations;

class AdminRegionType extends BaseType
{
    // the command "php artisan graphql:codegen" manages
    // the definitions between the special graphql comments following

    // GRAPHQL_GENERATED
    protected $attributes = [
        'name' => 'AdminRegion',
        'description' => "WE HAVE SOMETHING",
    ];

    public function fields()
    {
        return [
            'region' => [
                'type' => Type::nonNull(GraphQL::type('Region')),
            ],
            'regionQuarters' => [
                'type' => Type::nonNull(Type::listOf(GraphQL::type('RegionQuarter'))),
            ],
            'currentQuarter' => [
                'type' => GraphQL::type('RegionQuarter'),
            ],
            'adminQuarter' => [
                'type' => GraphQL::type('AdminRegionQuarter'),
                'args' => [
                    'quarterId' => [
                        'type' => Type::nonNull(Type::int()),
                    ],
                ],
            ],
        ];
    }
    // END_GRAPHQL_GENERATED

    public function resolve($root, $args, $context, ResolveInfo $info)
    {
        $region = $root['region'];

        /*
        $fields = $info->getFieldSelection($depth = 2);
        foreach ($fields as $field => $keys) {
            if ($field == 'regionQuarters')
        */

    }

    public function resolveRegionQuartersField($root)
    {
        $region = $root['region'];
        $regionQuarters = [];
        $allRqds = Models\RegionQuarterDetails::byRegion($region)->get();
        foreach ($allRqds as $rqd) {
            // todo find a neat way to reuse the RQD
            $regionQuarters[] = new Encapsulations\RegionQuarter($region, $rqd->quarter, $rqd);
        }

        return $regionQuarters;
    }

    // You need to fill out the following function to handle resolving.
    public function resolveAdminQuarterField($root, $args)
    {}

}
