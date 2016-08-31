<?php
namespace TmlpStats\Http\Controllers;

use TmlpStats\Region;

class ManageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function region($regionAbbr)
    {
        $region = Region::abbreviation($regionAbbr)->firstOrFail();
        $this->authorize('viewManageUi', $region);

        return view('admin.region', compact('region'));
    }
}
