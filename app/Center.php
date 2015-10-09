<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class Center extends Model
{
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
        'active' => 'bool',
    );

    public function getProgramManager()
    {
        return $this->getAccountable('programManager');
    }

    public function getClassroomLeader()
    {
        return $this->getAccountable('classroomLeader');
    }

    public function getT1TeamLeader()
    {
        return $this->getAccountable('team1TeamLeader');
    }

    public function getT2TeamLeader()
    {
        return $this->getAccountable('team2TeamLeader');
    }

    public function getMailingList($quarter = null)
    {
        return $this->getAccountable('Team Mailing List', $quarter);
    }

    public function getStatistician()
    {
        return $this->getAccountable('teamStatistician');
    }

    public function getStatisticianApprentice()
    {
        return $this->getAccountable('teamStatisticianApprentice');
    }

    public function getAccountable($accountability)
    {
        return Person::byAccountability($accountability)
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

    public function scopeByRegion($query, Region $region)
    {
        return $query->whereIn('region_id', function ($query) use ($region) {
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
