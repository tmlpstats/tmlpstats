<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class WithdrawCode extends Model {

    use CamelCaseModel;

    protected $fillable = array(
        'code',
        'display',
        'description',
    );

    public function scopeCode($query, $name)
    {
        return $query->whereCode($name);
    }

}
