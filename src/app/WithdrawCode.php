<?php
namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Traits\CachedRelationships;

class WithdrawCode extends Model
{
    use CamelCaseModel, CachedRelationships;

    protected $fillable = array(
        'code',
        'display',
        'description',
    );

    public function scopeCode($query, $name)
    {
        return $query->whereCode($name);
    }

    public function scopeActive($query, $active = true)
    {
        return $query->whereActive($active);
    }
}
