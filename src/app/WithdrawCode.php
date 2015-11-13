<?php
namespace TmlpStats;

class WithdrawCode extends ModelCachedRelationships
{
    protected $fillable = array(
        'code',
        'display',
        'description',
    );

    public function scopeCode($query, $name)
    {
        return $query->whereCode($name);
    }
}
