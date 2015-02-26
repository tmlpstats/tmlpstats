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
		if (!$request->has('cancel')) {
        	$user = User::create($request->all());
        	if ($request->has('is_admin')) {
        		$adminRole = Role::findByName('adminstrator');

        		if ($request->get('is_admin') == true) {
					$user->roles()->attach($adminRole->id);
        		} else {
					$user->roles()->detach($adminRole->id);
        		}
        	}
        	if ($request->has('active')) {
        		$user->active = $request->get('active') == true;
        	}
        	if ($request->has('require_password_reset')) {
        		$user->require_password_reset = $request->get('require_password_reset') == true;
        	}
        	$user->save();
		}
		return redirect('admin/users');
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

        return view('users.edit', compact('user', 'roles'));
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

        // Import Roles
    	if ($request->has('roles')) {
    		$user->updateRoles($request->get('roles'));
    	}
    	if ($request->has('active')) {
    		$user->active = $request->get('active') == true;
    	}
    	if ($request->has('require_password_reset')) {
    		$user->require_password_reset = $request->get('require_password_reset') == true;
    	}

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
}
