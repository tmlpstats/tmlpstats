<?php
namespace TmlpStats\Http\Requests;

use TmlpStats\Http\Requests\Request;
use Auth;

class QuarterRequest extends Request {

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
			'location'           => 'required|alpha|min:3',
			'distinction'        => 'required|in:Relatedness,Possibility,Opportunity,Action,Completion',
			'start_weekend_date' => 'required|date',
			'classroom1_date'    => 'required|date',
			'classroom2_date'    => 'required|date',
			'classroom3_date'    => 'required|date',
			'end_weekend_date'   => 'required|date',
		];
	}

}
