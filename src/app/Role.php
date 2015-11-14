<?php
namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Traits\CachedRelationships;

class Role extends Model
{
    use CamelCaseModel, CachedRelationships;

    protected $fillable = array(
        'name',
        'display',
    );

    public function scopeName($query, $name)
    {
        return $query->whereName($name);
    }

    public function users()
    {
        return $this->belongsToMany('TmlpStats\User', 'role_user')->withTimestamps();
    }
}
