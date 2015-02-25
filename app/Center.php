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

    public function users()
    {
        return $this->belongsToMany('TmlpStats\User', 'role_user')->withTimestamps();
    }

    public function statsReport()
    {
        return $this->hasMany('TmlpStats\StatsReport')->withTimestamps();
    }
}
