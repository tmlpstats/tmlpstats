<?php
namespace TmlpStats;

use Carbon\Carbon;
use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use TmlpStats\Traits\CachedRelationships;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract, AuthorizableContract
{
    use Authenticatable, Authorizable, CanResetPassword, CamelCaseModel, CachedRelationships;

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
        'managed',
    ];

    protected $casts = [
        'active' => 'boolean',
        'require_password_reset' => 'boolean',
        'managed' => 'boolean',
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

    /**
     * Getter
     *
     * Used mostly to abstract relationships
     *
     * @param string $name
     *
     * @return mixed|null|string
     */
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
            case 'fullName':
            case 'phone':
            case 'center':
                return $this->person
                    ? $this->person->$name
                    : null;
            case 'shortName':
                if ($this->managed) {
                    return $this->person->firstName;
                }

                return $this->person->shortName;
            default:
                return parent::__get($name);
        }
    }

    /**
     * Getter to be used for report-only users
     *
     * @param $name
     *
     * @return mixed|null|string
     */
    protected function getForReportUser($name)
    {
        switch ($name) {
            case 'firstName':
                return 'Guest';
            case 'reportToken':
                return $this->reportToken;
            case 'center':
                if ($this->reportToken && $this->reportToken->ownerType == Center::class) {
                    return $this->reportToken->getOwner();
                }

                return null;
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

    /**
     * Is Admin convenience function
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('administrator');
    }

    /**
     * Does user have provided accountability
     *
     * @param $name Accountability name
     *
     * @return bool
     */
    public function hasAccountability($name)
    {
        return $this->person
            ? $this->person->hasAccountability($name, Carbon::now())
            : false;
    }

    /**
     * Does user have provided role
     *
     * @param $name Role name
     *
     * @return bool
     */
    public function hasRole($name)
    {
        return ($this->role && $this->role->name === $name);
    }

    /**
     * Get the Region of the user's home center
     *
     * @param  boolean $globalOnly  If true, returns the global parent region
     * @return null|Region
     */
    public function homeRegion($globalOnly = false)
    {
        $region = null;
        if ($this->reportToken) {
            $owner = $this->reportToken->getOwner();

            if ($this->reportToken->ownerType == Region::class) {
                $region = $owner;
            }

            if ($this->reportToken->ownerType == Center::class && $owner) {
                $region = $owner->region;
            }

            if ($region && $globalOnly) {
                return $region->getParentGlobalRegion();
            }

            return $region;
        }

        if ($this->person && $this->person->center) {
            $region = $this->person->center->region;
        }

        if ($region && $globalOnly) {
            return $region->getParentGlobalRegion();
        }

        return $region;
    }

    /**
     * Set the user's center
     *
     * @param Center $center
     */
    public function setCenter($center)
    {
        if (!$this->person) {
            return;
        }

        $person = $this->person;
        $person->centerId = $center->id;
        $person->save();
    }

    /**
     * Set the user's First Name
     *
     * @param $firstName
     */
    public function setFirstName($firstName)
    {
        if (!$this->person) {
            return;
        }

        $person = $this->person;
        $person->firstName = $firstName;
        $person->save();
    }

    /**
     * Set the user's Last Name
     *
     * @param $lastName
     */
    public function setLastName($lastName)
    {
        if (!$this->person) {
            return;
        }

        $person = $this->person;
        $person->lastName = $lastName;
        $person->save();
    }

    /**
     * Set the user's phone number
     *
     * @param $phone
     */
    public function setPhone($phone)
    {
        if (!$this->person) {
            return;
        }

        $person = $this->person;
        $person->phone = $phone;
        $person->save();
    }

    /**
     * Set the user's email address
     *
     * @param $email
     */
    public function setEmail($email)
    {
        if (!$this->person) {
            return;
        }

        $person = $this->person;
        $person->email = $email;
        $person->save();
    }

    /**
     * Get the user's formatted phone number
     * e.g.
     *      (555) 555-5555
     *
     * @return string|void
     */
    public function formatPhone()
    {
        if (!$this->person) {
            return;
        }

        return $this->person->formatPhone();
    }

    public function toLocalTimezone(Carbon $date)
    {
        return $date->setTimezone($this->center->timezone);
    }

    public function scopeActive($query, $active = true)
    {
        return $query->whereActive($active);
    }

    public function scopeEmail($query, $email)
    {
        return $query->whereEmail($email);
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

    // special case of 'can' builtin for a specific purpose of getting user-global perms
    public function userCan($ability)
    {
        return $this->can($ability, $this);
    }
}
