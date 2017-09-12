<?php namespace TmlpStats\Api\Admin;

use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Domain;

/**
 * API Admin.System handles global settings information.
 */
class System extends AuthenticatedApiBase
{
    protected function assertSystemAdmin()
    {
        $user = $this->context->getUser();
        $this->assertAuthz($user->hasRole('administrator') || $user->hasRole('globalStatistician'));
    }

    public function allSystemMessages($data = [])
    {
        $this->assertSystemAdmin();

        return Models\SystemMessage::get()
            ->map([Domain\SystemMessage::class, 'fromModel']);
    }

    public function regionSystemMessages($section, Models\Region $region)
    {
        if (!$this->context->getUser()) {
            return [];
        }

        return Models\SystemMessage::active()
            ->section($section)
            ->region($region)
            ->get()
            ->map([Domain\SystemMessage::class, 'fromModel']);
    }

    public function writeSystemMessage($data = [])
    {
        $this->assertSystemAdmin();
        $domain = Domain\SystemMessage::fromArray($data, ['section', 'content']);

        if ($domain->id) {
            $message = Models\SystemMessage::findOrFail($domain->id);
        } else {
            $message = new Models\SystemMessage([
                'author_id' => $this->context->getUser()->id,
                'active' => true,
            ]);
        }

        $domain->fillModel($message);
        $message->save();

        return [
            'success' => true,
            'storedId' => $message->id,
        ];
    }

}
