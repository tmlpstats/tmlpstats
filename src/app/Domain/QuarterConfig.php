<?php
namespace TmlpStats\Domain;

use TmlpStats as Models;
use TmlpStats\Api\Exceptions\BadRequestException;

/**
 * Models a team application
 */
class QuarterConfig extends ParserDomain
{
    protected static $validProperties = [
        'location' => [
            'owner' => 'quarterLike',
            'type' => 'string',
            'options' => ['trim' => true],
        ],
        'startWeekendDate' => [
            'owner' => 'quarterLike',
            'type' => 'date',
        ],
        'endWeekendDate' => [
            'owner' => 'quarterLike',
            'type' => 'date',
        ],
        'classroom1Date' => [
            'owner' => 'quarterLike',
            'type' => 'date',
        ],
        'classroom2Date' => [
            'owner' => 'quarterLike',
            'type' => 'date',
        ],
        'classroom3Date' => [
            'owner' => 'quarterLike',
            'type' => 'date',
        ],
        'appRegFutureQuarterWeeks' => [
            'owner' => 'setting',
            'type' => 'int',
        ],
        'travelDueByDate' => [
            'owner' => 'setting',
            'type' => 'date',
        ],
    ];

    public function validate() {
        if ($this->classroom1Date !== null) {
            if ($this->classroom1Date->lt($this->startWeekendDate)) {
                throw new BadRequestException("classroom 1 must be after weekend");
            }

            if ($this->classroom2Date !== null) {
                if ($this->classroom2Date->lt($this->classroom1Date)) {
                    throw new BadRequestException("classroom 2 must be after classroom 1");
                }
                if ($this->classrom3Date !== null && $this->classroom3Date->lt($this->classroom2Date)) {
                    throw new BadRequestException("classroom 3 must be after classroom 2");
                }            

            }
        }
    }

    public function fillModel($quarterLike, $settingsScope)
    {

        foreach ($this->_values as $k => $v) {
            $conf = self::$validProperties[$k];
            switch ($conf['owner']) {
                case 'quarterLike':
                    $this->copyTarget($quarterLike, $k, $v, $conf);
                    break;
                case 'setting':
                    if ($v) {
                        if ($conf['type'] == 'date') {
                            $v = $v->toDateString();
                        }
                        Models\Setting::upsert(array_merge(['name' => $k, 'value' => $v], $settingsScope));
                    }
                    break;
            }
        }
    }
}