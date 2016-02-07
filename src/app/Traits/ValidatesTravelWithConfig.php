<?php
namespace TmlpStats\Traits;

use TmlpStats\Settings\Setting;

trait ValidatesTravelWithConfig
{
    /**
     * Is it time to check if travel and rooming are complete?
     *
     * @return bool
     */
    public function isTimeToCheckTravel()
    {
        $dueDate = Setting::name('travelDueByDate')
                          ->with($this->statsReport->center, $this->statsReport->quarter)
                          ->get();

        if (!$dueDate) {
            $dueDate = $this->statsReport->quarter->getClassroom2Date($this->statsReport->center);
        }

        return $this->statsReport->reportingDate->gt($dueDate);
    }
}
