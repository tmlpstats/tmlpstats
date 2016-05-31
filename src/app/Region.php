<?php
namespace TmlpStats;

use Carbon\Carbon;
use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Traits\CachedRelationships;

class Region extends Model
{
    use CamelCaseModel, CachedRelationships;

    protected $fillable = array(
        'abbreviation',
        'name',
        'email',
    );

    public function __get($name)
    {
        switch ($name) {
            case 'centers':
                $centers = [];
                $children = $this->getChildRegions();
                if ($children && !$children->isEmpty()) {
                    foreach ($children as $child) {
                        $centerList = $child->centers()->get();
                        foreach ($centerList as $center) {
                            if (!$center->active) {
                                continue;
                            }
                            $centers[$center->name] = $center;
                        }
                    }
                } else {
                    $centerList = $this->centers()->get();
                    foreach ($centerList as $center) {
                        if (!$center->active) {
                            continue;
                        }
                        $centers[$center->name] = $center;
                    }
                }
                ksort($centers);

                return array_values($centers);
            default:
                return parent::__get($name);
        }
    }

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

    public function getChildRegions()
    {
        if (!$this->isGlobalRegion()) {
            return null;
        } else {
            return Region::byParent($this)->get();
        }
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

    public function scopeIsGlobal($query)
    {
        return $query->whereNull('parent_id');
    }

    public function parent()
    {
        return $this->hasOne('TmlpStats\Region', 'id', 'parent_id');
    }

    public function centers()
    {
        return $this->hasMany('TmlpStats\Center');
    }

    public function reportTokens()
    {
        return $this->morphMany('TmlpStats\ReportToken', 'owner');
    }

    public function abbrLower()
    {
        return strtolower($this->abbreviation);
    }

    public function getUriRegionReport($reportingDate = null)
    {
        if ($reportingDate instanceof Carbon) {
            $reportingDate = $reportingDate->toDateString();
        }

        return action('ReportsController@getRegionReport', [
            'abbr' => $this->abbrLower(),
            'date' => $reportingDate,
        ]);
    }
}
