<?php
namespace TmlpStats\Tests\Traits;

use stdClass;
use TmlpStats\ModelCache;
use TmlpStats\Setting;

trait MocksSettings
{
    /**
     * List of all mocked settings
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Get the value of an already set setting
     *
     * @param $name
     *
     * @return null|mixed
     */
    public function getSetting($name)
    {
        return isset($this->settings[$name])
            ? $this->settings[$name]
            : null;
    }

    /**
     * Prepopulates the cache to force a set value
     *
     * @param     $name
     * @param     $value
     * @param int $centerId
     *
     * @return Setting
     */
    public function setSetting($name, $value, $centerId = 0)
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }

        $setting = $this->getSettingMock([
            'id'    => mt_rand(10000, 99999),
            'name'  => $name,
            'value' => $value,
        ]);

        ModelCache::create()->set($name, $centerId, $setting);

        return $this->settings[$name] = $setting;
    }

    /**
     * Forget a cached setting
     *
     * @param     $name
     * @param int $centerId
     */
    public function unsetSetting($name, $centerId = null)
    {
        ModelCache::create()->forget($name, $centerId);

        unset($this->settings[$name]);
    }

    /**
     * Clear all mocked settings
     */
    public function clearSettings()
    {
        foreach ($this->settings as $name => $value) {
            $this->unsetSetting($name);
        }
    }

    /**
     * Get a Setting object with the provided fields set
     *
     * @param array $data
     *
     * @return Setting
     */
    public function getSettingMock($data = [])
    {
        $setting = new Setting();

        if (isset($data['id'])) {
            $setting->id = $data['id'];
        }

        if (isset($data['name'])) {
            $setting->name = $data['name'];
        }

        if (isset($data['value'])) {
            $setting->value = $data['value'];
        }

        return $setting;
    }
}
