<?php
namespace TmlpStats\Api\Traits;

use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Validate\ApiValidationManager;

trait ValidatesObjects
{
    public function validateAll(Models\StatsReport $statsReport, array $data, array $pastWeeks = [])
    {
        $validator = new ApiValidationManager($statsReport);

        $success = $validator->run($data, $pastWeeks);

        return [
            'valid' => $success,
            'messages' => $validator->getMessages(),
        ];
    }

    public function validateObject(Models\StatsReport $statsReport, $object, $id = null, array $pastWeeks = [])
    {
        $validator = new ApiValidationManager($statsReport);

        $success = $validator->runOne($object, $id, $pastWeeks);

        return [
            'valid' => $success,
            'messages' => $validator->getMessages(),
        ];
    }
}
