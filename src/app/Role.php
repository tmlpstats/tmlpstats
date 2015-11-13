<?php
namespace TmlpStats;

class Role extends ModelCachedRelationships
{
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
