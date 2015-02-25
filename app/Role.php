<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class Role extends Model {

    use CamelCaseModel;

    protected $fillable = array(
        'name',
    );

    public function scopeName($query, $name)
    {
        return $query->whereName($name);
    }

    public function users()
    {
        return $this->belongsToMany('TmlpStats\User', 'role_user')->withTimestamps();
    }

}
