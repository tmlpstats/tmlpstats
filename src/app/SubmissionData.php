<?php

namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;

class SubmissionData extends Model
{
    use CamelCaseModel;

    protected $guarded = ['id'];
    protected $table = 'submission_data';
    protected $casts = [
        'data' => 'json',
        'reporting_date' => 'date',
    ];

    /**
     * Return the data coerced to the type defined by typeMapping.
     * The class in question has to have a static method fromArray
     * @return Data in the type defined by typeMapping
     */
    public function getTypedData()
    {
        $className = static::$typeMapping[$this->storedType]['class'];

        return $className::fromArray($this->data);
    }

    public function setTypedData($data)
    {
        $info = static::mappingForObj($data);
        if ($info === null) {
            // if we reach here, then we didn't find a type mapping
            throw new \Exception("Unknown type mapping {$className}");
        }

        $this->storedType = $info['storedType'];
        $this->storedId = $info['storedId'];
        $this->data = $data->toArray();
    }

    protected static function mappingForObj($data)
    {
        $className = get_class($data);
        foreach (static::$typeMapping as $k => $v) {
            if ($v['class'] == $className) {
                $idAttr = $v['idAttr'];

                return [
                    'storedType' => $k,
                    'storedId' => $data->$idAttr,
                ];
            }
        }

        return null;
    }

    public function scopeCenterDate($query, $center, $reportingDate)
    {
        if (!is_numeric($center)) {
            $center = $center->id;
        }

        return $query
            ->where('center_id', $center)
            ->where('reporting_date', $reportingDate);
    }

    // Scope by either the type code or the class name representing the type code.
    public function scopeType($query, $type)
    {

        return $query->where('stored_type', $type);
    }

}
