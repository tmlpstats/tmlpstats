<?php namespace TmlpStats\Api;

use App;
use Illuminate\Auth\Guard;
use Illuminate\Session\SessionManager;
use TmlpStats\Http\Controllers\Controller;
use Illuminate\Http\Request;
use TmlpStats as Models;

class Context {
    protected $user = null;
    protected $session = null;
    protected $request = null;

    public function __construct(Guard $auth, SessionManager $session, Request $request) {
        $this->user = $auth->user();
        $this->session = $session;
        $this->request = $request;
    }

    public function getCenter() {
        // TODO do this right, don't make it reliant on controller state, invert the paradigm.
        return App::make(Controller::class)->getCenter($this->request);
    }

    public function getRegion() {
        // TODO do this right, don't make it reliant on controller state, invert the paradigm.
        return App::make(Controller::class)->getRegion($this->request);
    }

    public function getRawSetting($name, $center=null) {
        if ($center == null){
            $center = $this->getCenter();
        }
        $setting = Models\Setting::get($name, $center);
        if ($setting != null) {
            return $setting->value;
        }
    }

    public function getSetting($name, $center=null) {
        $value = $this->getRawSetting($name, $center);
        if ($value === 'false') {
            return false;
        } else if ($value === 'true') {
            return true;
        }
        return $value;
    }
}
