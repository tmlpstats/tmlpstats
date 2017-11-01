<?php
namespace TmlpStats\GraphQL\Type;

use Folklore\GraphQL\Support\Type as BaseType;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use TmlpStats\Encapsulations;

class RegionQuarterType extends BaseType
{
    public function attributes()
    {
        return array_merge($this->attributes, [
            'resolveField' => function (Encapsulations\RegionQuarter $root, $args, $context, ResolveInfo $info) {
                $fieldName = $info->fieldName;
                switch ($fieldName) {
                    case 'milestone1Date':
                    case 'milestone2Date':
                    case 'milestone3Date':
                        $fieldName = str_replace('milestone', 'classroom', $fieldName);
                }

                return $root->{$fieldName};
            },
        ]);
    }
    // the command "php artisan graphql:codegen" manages
    // the definitions between the special graphql comments following
    // GRAPHQL_GENERATED
    protected $attributes = [
        'name' => 'RegionQuarter',
        'description' => "RegionQuarter represents the dates of a single quarter for a region.
You can think of this as the \'default\' dates.",
    ];

    public function fields()
    {
        return [
            'gid' => [
                'type' => Type::nonNull(Type::id()),
            ],
            'region' => [
                'type' => Type::nonNull(GraphQL::type('Region')),
            ],
            'quarterId' => [
                'type' => Type::nonNull(Type::int()),
            ],
            'startWeekendDate' => [
                'type' => GraphQL::type('Date'),
                'description' => "The Friday of the weekend before this quarter.",
            ],
            'endWeekendDate' => [
                'type' => GraphQL::type('Date'),
                'description' => "The Friday of the upcoming weekend.",
            ],
            'milestone1Date' => [
                'type' => GraphQL::type('Date'),
            ],
            'milestone2Date' => [
                'type' => GraphQL::type('Date'),
            ],
            'milestone3Date' => [
                'type' => GraphQL::type('Date'),
            ],
        ];
    }
    // END_GRAPHQL_GENERATED

    public function resolveQuarterIdField($root)
    {
        return $root->getQuarter()->id;
    }

}
