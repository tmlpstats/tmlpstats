<?php
namespace TmlpStats\Traits;

trait ScopedSettings
{
    protected static $_fetches = 0;
    protected $cachedSettings = null;

    /**
     * May be overridden by implementing class; get a single setting.
     * @return array  Must be a map of setting key to value.
     */
    protected function fetchAllScopedSettings()
    {
        $data = [];
        $settings = $this->scopedSettingBaseQuery()->get();
        foreach ($settings as $rawSetting) {
            $data[$rawSetting->name] = $rawSetting->value;
        }

        return $data;
    }

    /**
     * May be overridden by implementing class; get a single setting.
     * @param  string $setting [description]
     * @return mixed  -> One single setting's value.
     */
    protected function fetchOneScopedSetting(string $setting)
    {
        $rawSetting = $this->scopedSettingBaseQuery()->name($setting)->first();

        return ($rawSetting !== null) ? $rawSetting->value : null;

    }

    public function getScopedSetting(string $setting)
    {
        if (++static::$_fetches < 2) {
            return $this->fetchOneScopedSetting($setting);
        }
        $all = $this->getCachedSettings();

        return array_get($all, $setting, null);
    }

    public function getCachedSettings()
    {
        if (($v = $this->cachedSettings) === null) {
            $this->cachedSettings = $v = $this->fetchAllScopedSettings();
        }

        return $v;
    }
}
