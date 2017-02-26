<?php
namespace TmlpStats\Validate\Relationships;

use Cache;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Domain;
use TmlpStats\Validate\ApiValidatorAbstract;

class ApiAccountabilityValidator extends ApiValidatorAbstract
{
    protected $accountabilityCache = [];

    protected function validate($data)
    {
        // Initiate count array for ids 4 - 17
        $accountabilities = array_fill(4, 14, 0);

        foreach ($data['TeamMember'] as $member) {
            foreach ($member->accountabilities as $id) {
                $accountabilities[$id]++;
            }
        }

        // Make sure there is only one person with any given accountability
        foreach ($accountabilities as $id => $count) {
            if ($count > 1) {
                $this->addMessage('error', [
                    'id' => 'CLASSLIST_MULTIPLE_ACCOUNTABLES',
                    'ref' => ['type' => 'TeamMember', 'id' => $id],
                    'params' => ['accountability' => $this->getAccountability($id)->display],
                ]);
                $isValid = false;
            }
        }

        return $this->isValid;
    }

    /**
     * Get accountability object
     *
     * Using a cache to avoid multiple lookups within a single report validation
     *
     * @param  integer $id Accountability Id
     * @return Models\Accountability
     */
    protected function getAccountability($id)
    {
        if (!$this->accountabilityCache) {
            $this->accountabilityCache = Cache::remember('accountabilities', 10, function () {
                $allAccountabilities = Models\Accountability::get();
                return collect($allAccountabilities)->keyBy(function ($item) {
                    return $item->id;
                })->all();
            });
        }

        return isset($this->accountabilityCache[$id]) ? $this->accountabilityCache[$id] : null;
    }
}
