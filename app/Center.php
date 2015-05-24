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
