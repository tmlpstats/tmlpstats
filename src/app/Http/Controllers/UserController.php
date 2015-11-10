<?php
namespace TmlpStats\Http\Controllers;

use Illuminate\Http\Request;
use TmlpStats\User;
use TmlpStats\Role;
use TmlpStats\Center;
use TmlpStats\Person;
use TmlpStats\Region;
use TmlpStats\Http\Requests;
use TmlpStats\Http\Requests\UserRequest;

use Auth;
use DB;

class UserController extends Controller
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
     * @return Response
     */
    public function index()
    {
        $this->authorize('index', User::class);

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
        $this->authorize('create', User::class);

        $rolesObjects = Role::all();

        $selectedRole = null;
        $roles = array();
        foreach ($rolesObjects as $role) {
            $roles[$role->id] = $role->display;

            if ($role->name == 'readonly') {
                $selectedRole = $role->id;
            }
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

        return view('users.create', compact('centers', 'roles', 'selectedRole'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(UserRequest $request)
    {
        $this->authorize('create', User::class);

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

        return redirect('admin/users');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        $this->authorize($user);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        $this->authorize($user);

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
     * @param  int $id
     * @return Response
     */
    public function update(UserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        $this->authorize($user);

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
        if ($request->has('first_name')) {
            $user->setFirstName($request->get('first_name'));
        }
        if ($request->has('last_name')) {
            $user->setLastName($request->get('last_name'));
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

        $redirect = "admin/users/{$id}";
        if ($request->has('previous_url')) {
            $redirect = $request->get('previous_url');
        }

        return redirect($redirect);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function showProfile()
    {
        $user = Auth::user();

        $this->authorize($user);

        $roles = $user->roles;

        return view('users.edit', compact('user', 'roles'));
    }

    public function updateProfile(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $this->authorize($user);

        $user->update($request->all());
        $user->save();

        return redirect('user/profile');
    }
}
