<?php
namespace TmlpStats\Traits;

use Carbon\Carbon;
use TmlpStats\Setting;

trait ValidatesTravelWithConfig
{
    public function isTimeToCheckTravel()
    {
        $classroomNames = [
            'classroom1Date',
            'classroom2Date',
            'classroom3Date',
        ];

        $travelDueSetting = Setting::get('travelDueByDate', $this->statsReport->center);

        $dueDate = $this->statsReport->quarter->getClassroom2Date($this->statsReport->center);
        if ($travelDueSetting) {
            $settingValue = $travelDueSetting->value;
            if (in_array($settingValue, $classroomNames)) {
                $dueDate = $this->statsReport->quarter->$settingValue;
            } else if (preg_match('/^\d\d\d\d-\d\d-\d\d$/', $settingValue)) {
                $dueDate = Carbon::parse($settingValue);
            }
        }

        return $this->statsReport->reportingDate->gt($dueDate);
    }
}
