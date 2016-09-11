<?php
namespace TmlpStats\Tests\Unit\Traits;

use TmlpStats\ModelCache;
use TmlpStats\Setting;

trait MocksSettings
{
    /**
     * List of all mocked settings
     *
     * @var array
     */
    protected $mockedSettings = [];

    /**
     * Get the value of an already set setting
     *
     * @param $name
     *
     * @return null|mixed
     */
    public function getSetting($name)
    {
        return isset($this->mockedSettings[$name])
            ? $this->mockedSettings[$name]
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

        return $this->mockedSettings[$name] = $setting;
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

        unset($this->mockedSettings[$name]);
    }

    /**
     * Clear all mocked settings
     */
    public function clearSettings()
    {
        if (!$this->mockedSettings) {
            $this->mockedSettings = [];
            return;
        }

        foreach ($this->mockedSettings as $name => $value) {
            ModelCache::create()->forget($name);
        }

        $this->mockedSettings = [];
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
