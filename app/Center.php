<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class Center extends Model {

    use CamelCaseModel;

    protected $fillable = array(
        'name',
        'abbreviation',
        'team_name',
        'region_id',
        'stats_email',
        'active',
        'sheet_filename',
        'sheet_version',
        'timezone',
    );

    protected $casts = array(
        'active' => 'bool'
    );

    public function getProgramManager()
    {
        return Person::accountability('programManager')
            ->byCenter($this)
            ->first();
    }

    public function getClassroomLeader()
    {
        return Person::accountability('classroomLeader')
            ->byCenter($this)
            ->first();
    }

    public function getT1TeamLeader()
    {
        return Person::accountability('team1TeamLeader')
            ->byCenter($this)
            ->first();
    }

    public function getT2TeamLeader()
    {
        return Person::accountability('team2TeamLeader')
            ->byCenter($this)
            ->first();
    }

    public function getStatistician()
    {
        return Person::accountability('teamStatistician')
            ->byCenter($this)
            ->first();
    }

    public function getStatisticianApprentice()
    {
        return Person::accountability('teamStatisticianApprentice')
            ->byCenter($this)
            ->first();
    }

    public function getGlobalRegion()
    {
        if ($this->region && $this->region->parentId === null) {
            return $this->region;
        } else {
            return Region::find($this->region->parentId);
        }
    }

    public function getLocalRegion()
    {
        if ($this->region && $this->region->parentId !== null) {
            return $this->region;
        } else {
            return null;
        }
    }

    public function scopeName($query, $name)
    {
        return $query->whereName($name);
    }

    public function scopeAbbreviation($query, $abbr)
    {
        return $query->whereAbbreviation($abbr);
    }

    public function scopeActive($query)
    {
        return $query->whereActive(true);
    }

    public function scopeRegion($query, Region $region)
    {
        return $query->whereIn('region_id', function($query) use ($region) {
            $query->select('id')
                ->from('regions')
                ->where('id', $region->id)
                ->orWhere('parent_id', $region->id);
        });

    }

    public function people()
    {
        return $this->belongsToMany('TmlpStats\Person')->withTimestamps();
    }

    public function statsReports()
    {
        return $this->hasMany('TmlpStats\StatsReport');
    }

    public function region()
    {
        return $this->belongsTo('TmlpStats\Region');
    }
}
