<?php
namespace TmlpStats\Http\Controllers;

use Illuminate\Http\Request;
use TmlpStats as Models;
use TmlpStats\Http\Requests;
use TmlpStats\Http\Requests\CenterRequest;

use DateTimeZone;
use Log;

class AdminCenterController extends Controller
{
    /**
     * Authenticated admins only
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
        $this->authorize('index', Models\Center::class);

        $centers = Models\Center::orderBy('name', 'asc')->get();

        return view('admin.centers.index', compact('centers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->authorize('create', Models\Center::class);

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
        $this->authorize('store', Models\Center::class);

        $input = $request->all();

        if ($request->has('region')) {
            $region = Models\Region::abbreviation($request->get('region'))->first();
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

        Models\Center::create($input);

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
        $center = Models\Center::where('abbreviation', '=', $id)->firstOrFail();

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
        $center = Models\Center::where('abbreviation', '=', $id)->firstOrFail();

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
        $center = Models\Center::where('abbreviation', '=', $id)->firstOrFail();

        $this->authorize($center);

        $input = $request->all();

        if (!$request->has('active')) {
            $input['active'] = false;
        }

        if ($request->has('region')) {
            $region = Models\Region::abbreviation($request->get('region'))->first();
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
        if (!$request->has('centerIds') || !$request->get('centerIds')) {
            $this->pushResponse($request, false, 'No centers selected.');
            return;
        }

        $centerIds = $request->get('centerIds');

        // Update sheet version
        if ($request->has('sheetVersion')) {
            $sheetVersion = $request->get('sheetVersion');

            if (!preg_match("/\d+\.\d+\.\d+/", $sheetVersion)) {
                $this->pushResponse($request, false, 'Version provided is not valid. Please use format 1.2.3.');
                return;
            }

            if ($this->updateSheetVersion($centerIds, $sheetVersion)) {
                $this->pushResponse($request, true, 'Version updated successfully.');
            } else {
                $this->pushResponse($request, false, 'There was a problem updating one or more centers. Please try again.');
            }
        }

        // Update password
        if ($request->has('newPassword') && $request->has('confirmPassword')) {
            $password = $request->get('newPassword');

            if ($password !== $request->get('confirmPassword')) {
                $this->pushResponse($request, false, 'Passwords do not match. Please make sure to use the same password for both fields.');
                return;
            }

            if ($this->updatePassword($centerIds, $password)) {
                $this->pushResponse($request, true, 'Passwords updated successfully.');
            } else {
                $this->pushResponse($request, false, 'There was a problem updating one or more passwords. Please try again.');
            }
        }
    }

    public function pushResponse(Request $request, $success, $message)
    {
        $request->session()->flash('success', $success);
        $request->session()->flash('message', $message);
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

    protected function updateSheetVersion($centerIds, $sheetVersion)
    {
        $success = true;

        foreach ($centerIds as $id) {
            $center = Models\Center::find($id);
            if (!$center) {
                Log::error("Failed to update version for center {$id}. Center not found.");
                $success = false;
                continue;
            }
            $center->sheetVersion = $sheetVersion;
            if (!$center->save()) {
                Log::error("Failed to update version for center {$center->name}.");
                $success = false;
                continue;
            }

            Log::info("Updated version for {$center->name} to {$sheetVersion}.");
        }

        return $success;
    }

    protected function updatePassword($centerIds, $password)
    {
        $success = true;

        foreach ($centerIds as $id) {
            $center = Models\Center::find($id);
            if (!$center) {
                Log::error("Failed to update account password for center {$id}. Center not found.");
                $success = false;
                continue;
            }

            $user = Models\User::email($center->statsEmail)->first();
            if (!$user) {
                Log::error("Failed to update account password for center {$center->name}. Center stats user not found.");
                $success = false;
                continue;
            }

            $user->password = bcrypt($password);
            $user->rememberToken = null;
            if (!$user->save()) {
                Log::error("Failed to update password for {$user->email}.");
                $success = false;
                continue;
            }

            Log::info("Updated password for {$user->email}.");
        }

        return $success;
    }
}
