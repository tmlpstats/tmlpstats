<?php
namespace TmlpStats;

use App;
use Carbon\Carbon;
use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Api;
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

    public function getProgramManager(Carbon $date = null)
    {
        return $this->getAccountable('programManager', $date);
    }

    public function getClassroomLeader(Carbon $date = null)
    {
        return $this->getAccountable('classroomLeader', $date);
    }

    public function getT1TeamLeader(Carbon $date = null)
    {
        return $this->getAccountable('t1tl', $date);
    }

    public function getT2TeamLeader(Carbon $date = null)
    {
        return $this->getAccountable('t2tl', $date);
    }

    public function getStatistician(Carbon $date = null)
    {
        return $this->getAccountable('statistician', $date);
    }

    public function getStatisticianApprentice(Carbon $date = null)
    {
        return $this->getAccountable('statisticianApprentice', $date);
    }

    public function getAccountable($accountabilityName, Carbon $date = null)
    {
        $accountability = Accountability::name($accountabilityName)->first();

        if ($accountability === null) {
            return null;
        }

        return Person::byAccountability($accountability, $date)
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

    public function getMailingList(Quarter $quarter)
    {
        $list = App::make(Api\Context::class)->getSetting('centerReportMailingList', $this, $quarter);
        return $list ?: [];
    }

    public function setMailingList(Quarter $quarter, array $list)
    {
        $setting = Setting::firstOrNew([
            'center_id' => $this->id,
            'quarter_id' => $quarter->id,
            'name' => 'centerReportMailingList',
        ]);

        // No list and no existing setting. Nothing to do
        if (!$list && !$setting->exists) {
            return true;
        }

        // If the list is empty, remove the setting if it exists and abort
        if (!$list) {
            return $setting->delete();
        }

        // Canonicalize list
        $list = array_unique($list);
        sort($list);

        $setting->value = json_encode($list);
        return $setting->save();
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

    public function reportTokens()
    {
        return $this->morphMany('TmlpStats\ReportToken', 'owner');
    }

    public function getUriCenterReport($reportingDate = null)
    {
        if ($reportingDate instanceof Carbon) {
            $reportingDate = $reportingDate->toDateString();
        }

        return action('ReportsController@getCenterReport', [
            'abbr' => strtolower($this->abbreviation),
            'date' => $reportingDate,
        ]);
    }

    public function abbrLower()
    {
        return strtolower($this->abbreviation);
    }
}
