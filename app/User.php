<?php
namespace TmlpStats;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Eloquence\Database\Traits\CamelCaseModel;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword, CamelCaseModel;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'email',
		'password',
		'first_name',
		'last_name',
		'phone',
	];

    protected $casts = array(
        'active' => 'boolean',
    );


	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

    public function hasRole($name)
    {
        foreach($this->roles as $role) {
            if ($role->name == $name) {
                return true;
            }
        }
        return false;
    }

    public function hasCenter($centerId)
    {
        foreach($this->centers as $center) {
            if ($center->id == $centerId) {
                return true;
            }
        }
        return false;
    }

    public function homeRegion()
    {
        return count($this->centers) ? $this->centers[0]->globalRegion : null;
    }

    public function updateRoles($roles)
    {
        foreach ($roles as $roleId) {
            $role = Role::find($roleId);

            if ($role && !$this->hasRole($role->name)) {
                $this->roles()->attach($roleId);
            }
        }
        foreach ($this->roles as $existingRole) {
            if (!in_array($existingRole->id, $roles)) {
                $this->roles()->detach($existingRole->id);
            }
        }
    }

    public function updateCenters($centers)
    {
        foreach ($this->centers as $existingCenter) {
            if (!in_array($existingCenter->id, $centers)) {
                $this->centers()->detach($existingCenter->id);
            }
        }
        foreach ($centers as $centerAbbr) {
            $center = Center::abbreviation($centerAbbr)->first();

            if ($center && !$this->hasCenter($center->id)) {
                $this->centers()->attach($center->id);
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

    public function scopeActive($query)
    {
        return $query->where('active', '=', '1');
    }

    public function roles()
    {
        return $this->belongsToMany('TmlpStats\Role', 'role_user')->withTimestamps();
    }

    public function centers()
    {
        return $this->belongsToMany('TmlpStats\Center', 'center_user')->withTimestamps();
    }
}
