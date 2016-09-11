<?php
namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Traits\CachedRelationships;

class Accountability extends Model
{
    use CamelCaseModel, CachedRelationships;

    protected $fillable = array(
        'name',
        'context',
        'display',
    );

    protected $hidden = ['updated_at', 'created_at'];

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
        return $this->belongsToMany('TmlpStats\Person', 'accountability_person', 'accountability_id', 'person_id')
                    ->withPivot(['stats_at', 'ends_at'])
                    ->withTimestamps();
    }
}
