<?php

namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;

class TempScoreboardLog extends Model
{
    use CamelCaseModel;

    protected $table = 'temp_scoreboard_log';
    protected $guarded = ['id'];
}
