<?php
namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;

class SystemMessage extends Model
{
    use CamelCaseModel;

    protected $table = 'system_messages';
    protected $fillable = array(
        'center_id',
        'region_id',
        'author_id',
        'active',
        'section',
        'data',
    );

    protected $casts = [
        'data' => 'json',
        'active' => 'boolean',
    ];
    protected $visible = ['id', 'center_id', 'region_id', 'section', 'created_at'];

    public function scopeSection($query, $section)
    {
        return $query->where('section', '=', $section);
    }

    /**
     * Filter messages relevant to a given center.
     *
     * This gets messages for this center, this center's region, and global region (if different)
     */
    public function scopeCenter($query, Center $center)
    {
        return $query->where(function ($query) use ($center) {
            $region = $center->region;
            $query = $query->where('center_id', '=', $center->id)
                           ->orWhere('region_id', '=', $region->id);

            if ($region->parentId) {
                $query->orWhere('region_id', '=', $region->parentId);
            }
        });
    }

    public function scopeRegion($query, Region $region)
    {
        return $query->where(function ($query) use ($region) {
            $query = $query->where('region_id', '=', $region->id);
            if ($region->parentId) {
                $query->orWhere('region_id', '=', $region->parentId);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->whereActive(true);
    }

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class);
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), $this->data);
    }

    public static function centerActiveMessages($section, Center $center)
    {
        return self::section($section)->active()->center($center);
    }

}
