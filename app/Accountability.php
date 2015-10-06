<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class Accountability extends Model
{
    use CamelCaseModel;

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
        return $this->belongsToMany('TmlpStats\Person', 'accountability_person')->withTimestamps();
    }
}
