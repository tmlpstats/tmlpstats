<?php
namespace TmlpStats\Http\Requests;

use TmlpStats\Http\Requests\Request;
use Auth;

class UserRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return Auth::user()->hasRole('administrator');
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'first_name'             => 'required|max:255',
			'last_name'              => 'required|max:255',
			'phone'                  => 'regex:/^[\s\d\+\-\.]+$/',
			'email'                  => 'required|email|max:255|unique:users,id,'.$this->route('id'), // only enforce unique when id is not value provided
			'is_admin'               => 'boolean',
			'require_password_reset' => 'boolean',
			'active'                 => 'boolean',
		];
	}

}
