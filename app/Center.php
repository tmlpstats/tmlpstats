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
        'global_region',
        'local_region',
        'stats_email',
        'sheet_filename',
        'sheet_version',
        'active',
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

    public function getMailingList($quarter = null)
    {
        return $this->getAccountable('Team Mailing List', $quarter);
    }

    public function getStatistician($quarter = null)
    {
        return $this->getAccountable('Statistician', $quarter);
    }

    public function getStatisticianApprentice($quarter = null)
    {
        return $this->getAccountable('Statistician Apprentice', $quarter);
    }

    public function getAccountable($accountability, $quarter = null)
    {
        if (!$quarter) {
            $quarter = Quarter::current($this->globalRegion)->first();
        }

        return ProgramTeamMember::byCenter($this)
                                ->quarter($quarter)
                                ->accountability($accountability)
                                ->first();
    }

    public function scopeAbbreviation($query, $abbr)
    {
        return $query->whereAbbreviation($abbr);
    }

    public function scopeName($query, $name)
    {
        return $query->whereName($name);
    }

    public function scopeActive($query)
    {
        return $query->whereActive(true);
    }

    public function scopeGlobalRegion($query, $region)
    {
        return $query->whereGlobalRegion($region);
    }

    public function users()
    {
        return $this->belongsToMany('TmlpStats\User', 'center_user')->withTimestamps();
    }

    public function statsReports()
    {
        return $this->hasMany('TmlpStats\StatsReport');
    }
}
