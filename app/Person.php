<?php
namespace TmlpStats;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Eloquence\Database\Traits\CamelCaseModel;

class Person extends Model {

    use CamelCaseModel;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'center_id',
    ];

    public function hasAccountability($name)
    {
        foreach ($this->accountabilities as $accountability) {
            if ($accountability->name == $name) {
                return true;
            }
        }
        return false;
    }

    public function homeRegion()
    {
        return $this->center ? $this->center->region : null;
    }

    public function updateAccountbilities($accountabilities)
    {
        foreach ($accountabilities as $accountabilityId) {
            $accountability = Accountbility::find($accountabilityId);

            if ($accountability && !$this->hasAccountbility($accountability->name)) {
                $this->accountabilities()->attach($accountabilityId);
            }
        }
        foreach ($this->accountabilities as $existingAccountbility) {
            if (!in_array($existingAccountbility->id, $accountabilities)) {
                $this->accountabilities()->detach($existingAccountbility->id);
            }
        }
    }

    public function formatPhone()
    {
        // TODO: This handles the standard 10 digit North American phone number. Update to handle international formats
        if (isset($this->phone) && preg_match('/^(\d\d\d)[\s\.\-]?(\d\d\d)[\s\.\-]?(\d\d\d\d)$/', $this->phone, $matches)) {
            return "({$matches[1]}) {$matches[2]}-{$matches[3]}";
        }
        return $this->phone;
    }

    public function scopeCenter($query, $center)
    {
        return $query->whereCenterId($center->id);
    }

    public function accountabilities()
    {
        return $this->belongsToMany('TmlpStats\Accountability', 'accountability_person')->withTimestamps();
    }

    public function center()
    {
        return $this->belongsTo('TmlpStats\Center');
    }
}
