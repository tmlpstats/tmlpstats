<?php
namespace TmlpStats\Services;

use TmlpStats\User;
use Validator;
use Illuminate\Contracts\Auth\Registrar as RegistrarContract;

class Registrar implements RegistrarContract {

	/**
	 * Get a validator for an incoming registration request.
	 *
	 * @param  array  $data
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	public function validator(array $data)
	{
		return Validator::make($data, [
			'first_name' => 'required|max:255',
			'last_name'  => 'required|max:255',
			'phone'      => 'regex:/^[\s\d\+\-\.]+$/',
			'email'      => 'required|email|max:255|unique:users',
			'password'   => 'required|confirmed|min:6',
			'invite_code'=> 'required|in:GloabalStatisticiansRock2015'
		]);
	}

	/**
	 * Create a new user instance after a valid registration.
	 *
	 * @param  array  $data
	 * @return User
	 */
	public function create(array $data)
	{
		return User::create([
			'first_name' => $data['first_name'],
			'last_name'  => $data['last_name'],
			'phone'      => $data['phone'],
			'email'      => $data['email'],
			'password'   => bcrypt($data['password']),
		]);
	}

}
