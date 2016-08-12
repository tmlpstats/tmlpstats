<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\ApiBase;
use TmlpStats\Api\Exceptions as ApiExceptions;
use TmlpStats\Domain;

/**
 * Validation data
 */
class ValidationData extends ApiBase
{
    public function validate(Models\Center $center, Carbon $reportingDate)
    {
        if ($reportingDate->dayOfWeek !== Carbon::FRIDAY) {
            throw new ApiExceptions\BadRequestException('Reporting date must be a Friday.');
        }

        return ['success' => true];
    }
}
