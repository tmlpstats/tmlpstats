<?php

namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;

class SubmissionDataLog extends Model
{
    use CamelCaseModel;

    protected $guarded = ['id'];
    protected $table = 'submission_data_log';
    protected $casts = [
        'data' => 'json',
        'reporting_date' => 'date',
    ];

}
