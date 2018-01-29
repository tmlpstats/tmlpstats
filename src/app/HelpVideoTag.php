<?php
namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;

class HelpVideoTag extends Model
{
    use CamelCaseModel;

    protected $fillable = [
        'help_video_id',
        'name',
    ];

    public function scopeHelpVideo($query, $video)
    {
        if (is_object($video)) {
            $video = $video->id;
        }

        return $query->whereHelpVideoId($video);
    }

    public function scopeName($query, $name)
    {
        return $query->whereName($name);
    }
}
