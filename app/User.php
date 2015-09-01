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
        'person_id',
        'role_id',
        'active',
        'require_password_reset',
        'last_login_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'require_password_reset' => 'boolean',
    ];

    protected $dates = [
        'last_login_at' => 'date',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public function hasRole($name)
    {
        return ($this->role->name === $name);
    }

    public function homeRegion()
    {
        return $this->center ? $this->center->region : null;
    }

    public function getCenter()
    {
        return $this->person->center;
    }

    public function setRole($role)
    {
        $this->roleId = $role->id;
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
}
