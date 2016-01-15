<?php

namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use TmlpStats\Traits\CachedRelationships;

class Invite extends Model
{
    use CamelCaseModel, CachedRelationships, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
    ];

    protected $dates = [
        'email_sent_at',
    ];

    public function scopeToken($query, $token)
    {
        return $query->whereToken($token);
    }

    public function scopeByCenter($query, Center $center)
    {
        return $query->whereCenterId($center->id);
    }

    public function scopeByInvitedByUser($query, User $user)
    {
        return $query->whereInvitedByUserId($user->id);
    }

    public function scopeByRole($query, Role $role)
    {
        return $query->whereRoleId($role->id);
    }

    public function center()
    {
        return $this->belongsTo('TmlpStats\Center');
    }

    public function role()
    {
        return $this->belongsTo('TmlpStats\Role');
    }

    public function invitedByUser()
    {
        return $this->belongsTo('TmlpStats\User', 'invited_by_user_id');
    }
}
