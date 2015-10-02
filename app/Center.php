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

    public function getProgramManager($quarter = null)
    {
        return $this->getAccountable('Program Manager', $quarter);
    }

    public function getClassroomLeader($quarter = null)
    {
        return $this->getAccountable('Classroom Leader', $quarter);
    }

    public function getT1TeamLeader($quarter = null)
    {
        return $this->getAccountable('Team 1 Team Leader', $quarter);
    }

    public function getT2TeamLeader($quarter = null)
    {
        return $this->getAccountable('Team 2 Team Leader', $quarter);
    }

    public function getStatistician($quarter = null)
    {
        return $this->getAccountable('Statistician', $quarter);
    }

    public function getStatisticianApprentice($quarter = null)
    {
        return $this->getAccountable('Statistician Apprentice', $quarter);
    }

    // TODO: port this to new user scheme
    public function getAccountable($accountability, $quarter = null)
    {
        if (!$quarter) {
            $quarter = Quarter::current()->first();
            $quarter->setRegion($this->region);
        }

        return ProgramTeamMember::byCenter($this)
                                ->quarter($quarter)
                                ->accountability($accountability)
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
