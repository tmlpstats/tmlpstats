<?php namespace TmlpStats\Api;

use Illuminate\Http\Request;
use Session;

class UserProfile
{
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function setLocale($locale = '', $timezone = '')
    {
        // Save the user's timezone
        if ($timezone) {
            Session::put('timezone', $timezone);
        }
        // Save the user's locale
        if ($locale) {
            Session::put('locale', $locale);
        }

        return ['success' => true];
    }

    public function needsShim($v = '')
    {
        Session::set('needs_shim', $v);

        return [];
    }
}
