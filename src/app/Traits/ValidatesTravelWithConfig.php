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

        $dueDate = $this->statsReport->quarter->getClassroom2Date($this->statsReport->center);

        $travelDueSetting = Setting::get('travelDueByDate', $this->statsReport->center, $this->statsReport->quarter);
        if ($travelDueSetting) {
            $settingValue = $travelDueSetting->value;
            if (in_array($settingValue, $classroomNames)) {
                $dueDate = $this->statsReport->quarter->getQuarterDate($settingValue, $this->statsReport->center);
            } else if (preg_match('/^\d\d\d\d-\d\d-\d\d$/', $settingValue)) {
                $dueDate = Carbon::parse($settingValue);
            }
        }

        return $this->statsReport->reportingDate->gt($dueDate);
    }
}
