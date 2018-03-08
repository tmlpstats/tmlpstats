<?php
namespace TmlpStats\Http\Controllers;

use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\Request;
use Log;
use Redirect;
use TmlpStats as Models;
use Validator;

class AdminCenterController extends Controller
{
    protected $validationRules = [
        'name'         => 'required|max:255',
        'abbreviation' => 'required|max:5|unique:centers,abbreviation',
        'team_name'    => 'string|max:255',
        'stats_email'  => 'email',
        'active'       => 'boolean',
    ];

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
     * Input validation provided by Request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('store', Models\Center::class);

        $this->validate($request, $this->validationRules);

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

        $center = Models\Center::create($input);

        if ($request->has('mailing_list')) {
            $quarter = Models\Quarter::getQuarterByDate(Carbon::now(), $center->region);
            if (!$this->saveMailingList($request, $center, $quarter)) {
                return redirect('admin/centers/create')->withInput();
            }
        }

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
        $center = Models\Center::abbreviation($id)->firstOrFail();

        $this->authorize($center);

        $quarter = Models\Quarter::getQuarterByDate(Carbon::now(), $center->region);

        return view('admin.centers.show', compact('center', 'quarter'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $center = Models\Center::abbreviation($id)->firstOrFail();

        $this->authorize($center);

        $quarter = Models\Quarter::getQuarterByDate(Carbon::now(), $center->region);
        $timezones = DateTimeZone::listIdentifiers();
        $selectedTimezone = array_search($center->timezone, $timezones);

        if ($selectedTimezone === false) {
            $selectedTimezone = null;
        }

        return view('admin.centers.edit', compact('center', 'quarter', 'timezones', 'selectedTimezone'));
    }

    /**
     * Update the specified resource in storage.
     *
     * Input validation provided by Request
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $center = Models\Center::abbreviation($id)->firstOrFail();

        $this->authorize($center);

        // Add validation override so unique check ignores the current
        // center's abbreviation
        $validationRules = $this->validationRules;
        $validationRules['abbreviation'] .= ",{$id},abbreviation";

        $this->validate($request, $validationRules);

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

        // Save what we have so far. Mailing lists saved separately
        $center->update($input);

        // Don't check if there is a value first so we can remove existing lists
        $quarter = Models\Quarter::getQuarterByDate(Carbon::now(), $center->region);
        if (!$this->saveMailingList($request, $center, $quarter)) {
            return redirect("admin/centers/{$center->abbreviation}/edit")->withInput();
        }

        $redirect = "admin/centers";
        if ($request->has('previous_url')) {
            $redirect = $request->get('previous_url');
        }
        return redirect($redirect);
    }

    protected function saveMailingList(Request $request, Models\Center $center, Models\Quarter $quarter)
    {
        $mailingList = $request->get('mailing_list');
        $mailingList = explode(',', $mailingList);
        $mailingList = array_unique($mailingList);
        sort($mailingList);

        $list = [];
        $invalid = [];

        // Validate list
        foreach ($mailingList as $email) {
            $email = trim($email);

            if (!$email) {
                continue;
            }

            $validator = Validator::make(compact('email'), ['email' => 'required|email']);
            if ($validator->fails()) {
                $invalid[] = $email;
                Log::warning("Failed to include email '{$email}' in {$center->name}'s mailing list because it was not valid.");
                continue;
            }

            $list[] = $email;
        }

        // Set error message
        if ($invalid) {
            $this->pushResponse($request, false, 'The following emails were not added to the mailing list '
                . 'because they are not valid: '
                . implode(', ', $invalid));
        }

        return $center->setMailingList($quarter, $list) && !$invalid;
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
