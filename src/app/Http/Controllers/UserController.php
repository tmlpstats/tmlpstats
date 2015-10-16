<?php
namespace TmlpStats\Http\Controllers;

use DB;
use TmlpStats\User;
use TmlpStats\Role;
use TmlpStats\Center;
use TmlpStats\Person;
use TmlpStats\Region;
use TmlpStats\Http\Requests;
use TmlpStats\Http\Requests\UserRequest;

class UserController extends Controller {

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $users = User::active()
            ->select('users.*', 'people.first_name', 'people.last_name', 'people.phone', 'people.email')
            ->join('people', 'people.id', '=', 'users.person_id')
            ->orderby('people.first_name')
            ->orderby('people.last_name')
            ->get();

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $rolesObjects = Role::all();

        $roles = array();
        foreach ($rolesObjects as $role) {
            $roles[$role->id] = $role->display;
        }

        $centerList = DB::table('centers')
            ->select('centers.*', DB::raw('regions.name as regionName'), 'regions.parent_id')
            ->join('regions', 'regions.id', '=', 'centers.region_id')
            ->get();

        $centers = array();
        foreach ($centerList as $center) {
            $parent = ($center->parent_id)
                ? Region::find($center->parent_id)
                : null;

            $regionName = ($parent)
                ? $parent->name
                : $center->regionName;

            $centers[$center->abbreviation] = "{$regionName} - {$center->name}";
        }
        asort($centers);

        return view('users.create', compact('centers', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(UserRequest $request)
    {
        $redirect = 'admin/users';

        if ($request->has('cancel')) {
            return redirect($redirect);
        }

        $input = $request->all();

        $person = Person::create($input);
        $input['person_id'] = $person->id;

        $user = User::create($input);

        if ($request->has('center')) {
            $center = Center::abbreviation($request->get('center'))->first();
            if ($center) {
                $user->setCenter($center);
            }
        }
        if ($request->has('role')) {
            $role = Role::find($request->get('role'));
            if ($role) {
                $user->roleId = $role->id;
            }
        }
        if ($request->has('active')) {
            $user->active = $request->get('active') == true;
        }
        if ($request->has('require_password_reset')) {
            $user->requirePasswordReset = $request->get('require_password_reset') == true;
        }
        $user->save();

        return redirect($redirect);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $rolesObjects = Role::all();

        $roles = array();
        foreach ($rolesObjects as $role) {
            $roles[$role->id] = $role->display;
        }

        $centerList = DB::table('centers')
            ->select('centers.*', DB::raw('regions.name as regionName'), 'regions.parent_id')
            ->join('regions', 'regions.id', '=', 'centers.region_id')
            ->get();

        $centers = array();
        foreach ($centerList as $center) {
            $parent = ($center->parent_id)
                ? Region::find($center->parent_id)
                : null;

            $regionName = ($parent)
                ? $parent->name
                : $center->regionName;

            $centers[$center->abbreviation] = "{$regionName} - {$center->name}";
        }
        asort($centers);

        return view('users.edit', compact('user', 'roles', 'centers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(UserRequest $request, $id)
    {
        $redirect = 'admin/users/' . $id;
           if ($request->has('previous_url')) {
            $redirect = $request->get('previous_url');
        }

        if ($request->has('cancel')) {
            return redirect($redirect);
        }

        $user = User::findOrFail($id);
        $user->update($request->all());

        if ($request->has('center')) {
            $center = Center::abbreviation($request->get('center'))->first();
            if ($center) {
                $user->setCenter($center);
            }
        }
        if ($request->has('role')) {
            $role = Role::find($request->get('role'));
            if ($role) {
                $user->roleId = $role->id;
            }
        }
        if ($request->has('phone')) {
            $user->setPhone($request->get('phone'));
        }
        if ($request->has('email')) {
            $user->setEmail($request->get('email'));
        }
        if ($request->has('active')) {
            $user->active = $request->get('active') == true;
        }
        if ($request->has('require_password_reset')) {
            $user->requirePasswordReset = $request->get('require_password_reset') == true;
        }
        $user->save();

        return redirect($redirect);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function showProfile()
    {
        $user = Auth::user();
        $roles = $user->roles;
        $showPasswordUpdate = true;

        return view('users.edit', compact('user', 'roles'));
    }

    public function updateProfile()
    {
        $redirect = 'user/profile';

        if ($request->has('cancel')) {
            return redirect($redirect);
        }

        $user = User::findOrFail($id);
        $user->update($request->all());

        $user->save();

        return redirect($redirect);
    }
}
