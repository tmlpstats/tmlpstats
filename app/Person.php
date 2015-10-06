<?php
namespace TmlpStats;

use TmlpStats\Center;
use Illuminate\Database\Eloquent\Model;
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

    public function hasAccountability(Accountability $accountability)
    {
        foreach ($this->accountabilities as $myAccountability) {
            if ($myAccountability->id == $accountability->id) {
                return true;
            }
        }
        return false;
    }

    public function addAccountability(Accountability $accountability)
    {
        if (!$this->hasAccountability($accountability)){
            $this->accountabilities()->attach($accountability->id);
        }
    }

    // This isn't complete. Fix it when we need it
//    public function setAccountbilities($accountabilities)
//    {
//        foreach ($accountabilities as $accountability) {
//            if (!$this->hasAccountbility($accountability)) {
//                $this->accountabilities()->attach($accountability->id);
//            }
//        }
//        foreach ($this->accountabilities as $existingAccountbility) {
//            if (!in_array($existingAccountbility->id, $accountabilities)) {
//                $this->accountabilities()->detach($existingAccountbility->id);
//            }
//        }
//    }

    public function homeRegion()
    {
        return $this->center ? $this->center->region : null;
    }

    public function formatPhone()
    {
        // TODO: This handles the standard 10 digit North American phone number. Update to handle international formats
        if (isset($this->phone) && preg_match('/^(\d\d\d)[\s\.\-]?(\d\d\d)[\s\.\-]?(\d\d\d\d)$/', $this->phone, $matches)) {
            return "({$matches[1]}) {$matches[2]}-{$matches[3]}";
        }
        return $this->phone;
    }

    public function scopeFirstName($query, $name)
    {
        return $query->whereFirstName($name);
    }

    public function scopeLastName($query, $name)
    {
        return $query->whereLastName($name);
    }

    public function scopeByCenter($query, Center $center)
    {
        return $query->whereCenterId($center->id);
    }

    public function scopeAccountability($query, $accountability)
    {
        return $query->whereHas('accountabilities', function ($query) use ($accountability) {
            $query->whereName($accountability);
        });
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
