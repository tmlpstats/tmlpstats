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

    // Statistician, T1TL, T2TL, PM, CL
    // Does not include Stats Apprentice
    // TODO: does not include PM/CL. add them
    protected $requiredAccountability = [4, 6, 7];

    protected function validate($data)
    {
        // Initiate count array for ids 4 - 17
        $accountabilities = array_fill(4, 14, 0);

        foreach ($data['TeamMember'] as $member) {
            if (!$member->accountabilities) {
                continue;
            }

            foreach ($member->accountabilities as $id) {
                $accountabilities[$id]++;
            }
        }

        // Make sure there is only one person with any given accountability
        foreach ($accountabilities as $id => $count) {
            if ($count > 1) {
                $this->addMessage('error', [
                    'id' => 'CLASSLIST_MULTIPLE_ACCOUNTABLES',
                    'ref' => ['type' => 'Accountability', 'id' => $id],
                    'params' => ['accountability' => $this->getAccountability($id)->display],
                ]);
                $isValid = false;
            } else if ($count === 0 && in_array($id, $this->requiredAccountability)) {
                $this->addMessage('warning', [
                    'id' => 'CLASSLIST_MISSING_ACCOUNTABLE',
                    'ref' => ['type' => 'Accountability', 'id' => $id],
                    'params' => ['accountability' => $this->getAccountability($id)->display],
                ]);
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
