<?php
namespace TmlpStats\Http\Controllers;

use TmlpStats\User;
use TmlpStats\Role;
use TmlpStats\Center;
use TmlpStats\Http\Requests;
use TmlpStats\Http\Requests\UserRequest;
use TmlpStats\Http\Controllers\Controller;

use Illuminate\Http\Request;

class UserController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
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
		$users = User::active()->get();

        return view('users.index', compact('users'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
        $roles = Role::all();

        return view('users.create', compact('roles'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(UserRequest $request)
	{
        $redirect = 'admin/users';

		if (!$request->has('cancel')) {
            return redirect($redirect);
        }

    	$user = User::create($request->all());

        if ($request->has('roles')) {
            $user->updateRoles($request->get('roles'));
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
        $roles = Role::all();

        return view('users.show', compact('user', 'roles'));
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
        $roles = Role::all();
        $centerList = Center::orderBy('global_region')->orderBy('name')->get();
        $centers = array();
        foreach ($centerList as $center) {
            $centers[$center->abbreviation] = "{$center->globalRegion} - {$center->name}";
        }

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
            $user->updateCenters(array($request->get('center')));
        }
        if ($request->has('roles')) {
            $user->updateRoles($request->get('roles'));
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
