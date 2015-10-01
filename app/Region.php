<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class Region extends Model {

    use CamelCaseModel;

    protected $fillable = array(
        'abbreviation',
        'name',
    );

    public function scopeAbbreviation($query, $abbreviation)
    {
        return $query->whereAbbreviation($abbreviation);
    }

    public function scopeName($query, $name)
    {
        return $query->whereName($name);
    }

    public function scopeParent($query, Region $parent)
    {
        return $query->whereParentId($parent->id);
    }

    public function parent()
    {
        return $this->hasOne('TmlpStats\Region', 'id', 'parent_id');
    }
}
