<?php namespace TmlpStats\Api;

use App;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use TmlpStats as Models;
use TmlpStats\Http\Controllers\Controller;

/**
 * Context is decisive.
 */
class Context
{
    protected $user = null;
    protected $request = null;

    public function __construct(Guard $auth, Request $request)
    {
        $this->user = $auth->user();
        $this->request = $request;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getCenter()
    {
        // TODO do this right, don't make it reliant on controller state, invert the paradigm.
        return App::make(Controller::class)->getCenter($this->request);
    }

    public function getRegion()
    {
        // TODO do this right, don't make it reliant on controller state, invert the paradigm.
        return App::make(Controller::class)->getRegion($this->request);
    }

    public function getRawSetting($name, $center = null)
    {
        if ($center == null) {
            $center = $this->getCenter();
        }
        $setting = Models\Setting::get($name, $center);
        if ($setting != null) {
            return $setting->value;
        }
    }

    public function getSetting($name, $center = null)
    {
        $value = $this->getRawSetting($name, $center);
        if ($value === 'false') {
            return false;
        } else if ($value === 'true') {
            return true;
        }
        return $value;
    }
}
