<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

// TODO: delete me after migration
class TmlpGame extends Model {

    use CamelCaseModel;

    protected $fillable = [
        'center_id',
        'type',
    ];

    public function center()
    {
        return $this->belongsTo('TmlpStats\Center');
    }

    public function gameData()
    {
        return $this->hasMany('TmlpStats\TmlpGameData');
    }
}
