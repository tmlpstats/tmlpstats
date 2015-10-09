<?php
namespace TmlpStats;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Eloquence\Database\Traits\CamelCaseModel;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
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
        'active'                 => 'boolean',
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

    public function __get($name)
    {
        switch ($name) {
            case 'firstName':
            case 'lastName':
            case 'phone':
            case 'email':
            case 'center':
                // TODO: remove me. needed for migration
                if (!$this->person) {
                    return parent::__get($name);
                }
                return $this->person->$name;
            default:
                return parent::__get($name);
        }
    }

    public function hasAccountability($name)
    {
        return $this->person->hasAccountability($name);
    }

    public function hasRole($name)
    {
        return ($this->role->name === $name);
    }

    public function homeRegion()
    {
        return $this->person->center ? $this->person->center->region : null;
    }

    public function setCenter($center)
    {
        $person = $this->person;
        $person->centerId = $center->id;
        $person->save();
    }

    public function setPhone($phone)
    {
        $person = $this->person;
        $person->phone = $phone;
        $person->save();
    }

    public function setEmail($email)
    {
        $person = $this->person;
        $person->email = $email;
        $person->save();
    }

    public function formatPhone()
    {
        // TODO: This handles the standard 10 digit North American phone number. Update to handle international formats
        if (isset($this->person->phone) && preg_match('/^(\d\d\d)[\s\.\-]?(\d\d\d)[\s\.\-]?(\d\d\d\d)$/', $this->person->phone, $matches)) {
            return "({$matches[1]}) {$matches[2]}-{$matches[3]}";
        }
        return $this->person->phone;
    }

    public function scopeActive($query, $active = true)
    {
        return $query->whereActive($active);
    }

    public function scopeByPerson($query, Person $person)
    {
        return $query->wherePersonId($person->id);
    }

    public function scopeByRole($query, Role $role)
    {
        return $query->whereRoleId($role->id);
    }

    public function person()
    {
        return $this->belongsTo('TmlpStats\Person');
    }

    public function role()
    {
        return $this->belongsTo('TmlpStats\Role');
    }
}
