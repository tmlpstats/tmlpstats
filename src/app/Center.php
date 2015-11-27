<?php
namespace TmlpStats;

use Carbon\Carbon;
use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Traits\CachedRelationships;

class Center extends Model
{
    use CamelCaseModel, CachedRelationships;

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
        return $this->getAccountable('t1tl');
    }

    public function getT2TeamLeader()
    {
        return $this->getAccountable('t2tl');
    }

    public function getStatistician()
    {
        return $this->getAccountable('statistician');
    }

    public function getStatisticianApprentice()
    {
        return $this->getAccountable('statisticianApprentice');
    }

    public function getAccountable($accountabilityName)
    {
        $accountability = Accountability::name($accountabilityName)->first();

        if ($accountability === null) {
            return null;
        }

        return Person::byAccountability($accountability)
            ->byCenter($this)
            ->first();
    }

    /**
     * Get all team members that have reported in an official report for this center
     *
     * @return mixed
     */
    public function getTeamRoster()
    {
        $statsReports = $this->statsReports()
            ->currentQuarter($this->region)
            ->orderBy('submitted_at')
            ->groupBy('reporting_date')
            ->get();

        $memberIds = [];
        foreach ($statsReports as $report) {
            $membersData = TeamMemberData::byStatsReport($report)->get();
            foreach ($membersData as $data) {
                $memberIds[] = $data->teamMember->id;
            }
        }
        $members = TeamMember::whereIn('id', array_unique($memberIds))->get();
        return $members;
    }

    public function getGlobalRegion()
    {
        if ($this->region->isGlobalRegion()) {
            return $this->region;
        } else {
            return $this->region->getParentGlobalRegion();
        }
    }

    public function getLocalRegion()
    {
        if (!$this->region->isGlobalRegion()) {
            return $this->region;
        } else {
            return null;
        }
    }

    public function getLocalTime(Carbon $time)
    {
        $time->setTimezone($this->timezone);
        return $time;
    }

    public function inRegion(Region $region)
    {
        return $this->region->inRegion($region);
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
