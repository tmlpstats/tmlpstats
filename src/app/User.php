<?php
namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Traits\CachedRelationships;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, CamelCaseModel, CachedRelationships;

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
        'last_login_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    protected $reportToken = null;

    public function __get($name)
    {
        if ($this->reportToken) {
            return $this->getForReportUser($name);
        } else if ($name == 'reportToken') {
            return null;
        }

        switch ($name) {
            case 'firstName':
            case 'lastName':
            case 'phone':
            case 'center':
                return $this->person
                    ? $this->person->$name
                    : null;
            default:
                return parent::__get($name);
        }
    }

    protected function getForReportUser($name)
    {
        switch ($name) {
            case 'firstName':
                return 'Guest';
            case 'reportToken':
                return $this->reportToken;
            case 'center':
                return $this->reportToken
                    ? $this->reportToken->center
                    : null;
            case 'lastName':
            case 'phone':
                return null;
            default:
                return parent::__get($name);
        }
    }

    public function setReportToken(ReportToken $reportToken)
    {
        $this->reportToken = $reportToken;
    }

    public function isAdmin()
    {
        return $this->hasRole('administrator');
    }

    public function hasAccountability($name)
    {
        return $this->person
            ? $this->person->hasAccountability($name)
            : false;
    }

    public function hasRole($name)
    {
        return ($this->role && $this->role->name === $name);
    }

    public function homeRegion()
    {
        if ($this->reportToken) {
            return $this->reportToken->center
                ? $this->reportToken->center->region
                : null;
        }

        return $this->person && $this->person->center
            ? $this->person->center->region
            : null;
    }

    public function setCenter($center)
    {
        if (!$this->person) {
            return;
        }

        $person = $this->person;
        $person->centerId = $center->id;
        $person->save();
    }

    public function setFirstName($firstName)
    {
        if (!$this->person) {
            return;
        }

        $person = $this->person;
        $person->firstName = $firstName;
        $person->save();
    }

    public function setLastName($lastName)
    {
        if (!$this->person) {
            return;
        }

        $person = $this->person;
        $person->lastName = $lastName;
        $person->save();
    }

    public function setPhone($phone)
    {
        if (!$this->person) {
            return;
        }

        $person = $this->person;
        $person->phone = $phone;
        $person->save();
    }

    public function setEmail($email)
    {
        if (!$this->person) {
            return;
        }

        $person = $this->person;
        $person->email = $email;
        $person->save();
    }

    public function formatPhone()
    {
        if (!$this->person) {
            return;
        }

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
