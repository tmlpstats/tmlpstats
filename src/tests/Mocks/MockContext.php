<?php
namespace TmlpStats\Tests\Mocks;

use App;
use TmlpStats\Api\Context;

class MockContext extends Context
{
    public $canCalls;
    private $overrideCan = null;
    private $overrideSettings;

    public function __construct()
    {
        $this->user = null;
        $this->request = null;
        $this->canCalls = [];
        $this->overrideSettings = [];
    }

    public static function defaults()
    {
        return new static();
    }

    public function install()
    {
        App::instance(Context::class, $this);

        return $this;
    }

    public function withUser($user)
    {
        $this->user = $user;

        return $this;
    }

    public function withOverrideCan($f)
    {
        $this->overrideCan = $f;

        return $this;
    }

    public function withFakedAdmin()
    {
        return $this->withOverrideCan(function ($a, $b) {
            return true;
        });
    }

    public function withCenter($center)
    {
        $this->setCenter($center);

        return $this;
    }

    public function can($priv, $target)
    {
        $this->canCalls[] = [$priv, $target];
        if ($this->overrideCan !== null) {
            return call_user_func($this->overrideCan, $priv, $target);
        } else if ($this->user === null) {
            return false;
        } else {
            return parent::can($priv, $target);
        }
    }

    public function clearEncapsulations()
    {
        $this->encapsulations = [];
    }

    public function withSetting($key, $value)
    {
        $this->overrideSettings[$key] = $value;

        return $this;
    }

    public function getSetting($name, $center = null, $quarter = null)
    {
        if (array_key_exists($name, $this->overrideSettings)) {
            return $this->overrideSettings[$name];
        } else {
            return parent::getSetting($name, $center, $quarter);
        }
    }
}
