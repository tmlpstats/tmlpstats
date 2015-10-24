<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class Region extends Model
{
    use CamelCaseModel;

    protected $fillable = array(
        'abbreviation',
        'name',
        'email',
    );

    public function isGlobalRegion()
    {
        return $this->parentId === null;
    }

    public function getParentGlobalRegion()
    {
        if ($this->parentId) {
            return $this->parent;
        }
        return $this;
    }

    public function inRegion(Region $region)
    {
        return ($region->id == $this->id
            || $region->id == $this->parentId);
    }

    public function scopeAbbreviation($query, $abbreviation)
    {
        return $query->whereAbbreviation($abbreviation);
    }

    public function scopeName($query, $name)
    {
        return $query->whereName($name);
    }

    public function scopeByParent($query, Region $parent)
    {
        return $query->whereParentId($parent->id);
    }

    public function parent()
    {
        return $this->hasOne('TmlpStats\Region', 'id', 'parent_id');
    }
}
