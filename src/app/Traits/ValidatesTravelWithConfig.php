<?php
namespace TmlpStats\Traits;

use App;
use TmlpStats\Api;
use TmlpStats\Domain;
use TmlpStats\Domain\Logic;

trait ValidatesTravelWithConfig
{
    /**
     * Is it time to check if travel and rooming are complete?
     *
     * @return bool
     */
    public function isTimeToCheckTravel()
    {
        if (($center = $this->statsReport->center) !== null) {
            $cq = Domain\CenterQuarter::ensure($center, $this->statsReport->quarter);
            $dueDate = $cq->getTravelDueByDate();
        } else {
            // XXX all the validator unit tests use a null center, so we will keep this in place until the next iteration on cleaning up settings.
            $dueDateRaw = App::make(Api\Context::class)->getSetting('travelDueByDate', $this->statsReport->center, $this->statsReport->quarter);

            if ($dueDateRaw) {
                $dueDate = Logic\QuarterDates::parseQuarterDate($dueDateRaw, $this->statsReport->quarter, $this->statsReport->center);
            } else {
                $dueDate = $this->statsReport->quarter->getClassroom2Date($this->statsReport->center);
            }
        }

        return $this->statsReport->reportingDate->gt($dueDate);
    }
}
