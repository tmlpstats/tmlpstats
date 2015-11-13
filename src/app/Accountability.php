<?php
namespace TmlpStats;

class Accountability extends ModelCachedRelationships
{
    protected $fillable = array(
        'name',
        'context',
        'display',
    );

    public function scopeName($query, $name)
    {
        return $query->whereName($name);
    }

    public function scopeContext($query, $context)
    {
        return $query->whereContext($context);
    }

    public function people()
    {
        return $this->belongsToMany('TmlpStats\Person', 'accountability_person', 'accountability_id', 'person_id')->withTimestamps();
    }
}
