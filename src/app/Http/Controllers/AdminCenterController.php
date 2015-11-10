<?php
namespace TmlpStats\Http\Controllers;

use Illuminate\Http\Request;
use TmlpStats\Center;
use TmlpStats\Region;
use TmlpStats\Http\Requests;
use TmlpStats\Http\Requests\CenterRequest;

use DateTimeZone;
use Log;

class AdminCenterController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:administrator');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('index', Center::class);

        $centers = Center::orderBy('name', 'asc')->get();

        return view('admin.centers.index', compact('centers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->authorize('create', Center::class);

        $selectedRegion = $this->getRegion($request);

        $timezones = DateTimeZone::listIdentifiers();

        return view('admin.centers.create', compact('selectedRegion', 'timezones'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CenterRequest $request)
    {
        $this->authorize('store', Center::class);

        $input = $request->all();

        if ($request->has('region')) {
            $region = Region::abbreviation($request->get('region'))->first();
            if ($region) {
                $input['region_id'] = $region->id;
            }
        }

        if ($request->has('timezone')) {
            $timezoneList = DateTimeZone::listIdentifiers();
            if (isset($timezoneList[$request->get('timezone')])) {
                $input['timezone'] = $timezoneList[$request->get('timezone')];
            }
        }

        Center::create($input);

        return redirect('admin/centers');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $center = Center::where('abbreviation', '=', $id)->firstOrFail();

        $this->authorize($center);

        return view('admin.centers.show', compact('center'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $center = Center::where('abbreviation', '=', $id)->firstOrFail();

        $this->authorize($center);

        $timezones = DateTimeZone::listIdentifiers();
        $selectedTimezone = array_search($center->timezone, $timezones);

        if ($selectedTimezone === false) {
            $selectedTimezone = null;
        }

        return view('admin.centers.edit', compact('center', 'timezones', 'selectedTimezone'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(CenterRequest $request, $id)
    {
        $center = Center::where('abbreviation', '=', $id)->firstOrFail();

        $this->authorize($center);

        $input = $request->all();

        if (!$request->has('active')) {
            $input['active'] = false;
        }

        if ($request->has('region')) {
            $region = Region::abbreviation($request->get('region'))->first();
            if ($region) {
                $input['region_id'] = $region->id;
            }
        }

        if ($request->has('timezone')) {
            $timezoneList = DateTimeZone::listIdentifiers();
            if (isset($timezoneList[$request->get('timezone')])) {
                $input['timezone'] = $timezoneList[$request->get('timezone')];
            }
        }

        $center->update($input);

        $redirect = "admin/centers";
        if ($request->has('previous_url')) {
            $redirect = $request->get('previous_url');
        }
        return redirect($redirect);
    }

    public function batchUpdate(Request $request)
    {
        if ($request->has('sheetVersion') && $request->has('centerIds')) {
            $centerIds = $request->get('centerIds');
            if ($centerIds) {
                $sheetVersion = $request->get('sheetVersion');
                if (!preg_match("/\d+\.\d+\.\d+/", $sheetVersion)) {
                    Log::warn("Invalid sheet version {$sheetVersion}.");
                    return;
                }
                foreach ($centerIds as $id) {
                    $center = Center::find($id);
                    if (!$center) {
                        Log::warn("Unable to update center {$id}. Center not found.");
                        continue;
                    }
                    $center->sheetVersion = $sheetVersion;
                    $center->save();
                }
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
