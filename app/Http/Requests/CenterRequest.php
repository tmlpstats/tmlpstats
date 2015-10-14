<?php
namespace TmlpStats\Http\Requests;

use TmlpStats\Http\Requests\Request;
use Auth;

class CenterRequest extends Request {

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
            'name'           => 'required|max:255',
            'abbreviation'   => 'required|max:5|unique:centers,id,'.$this->request->get('id'), // only enforce unique when id is not value provided
            'team_name'      => 'string|max:255',
            'global_region'  => 'in:NA,EME,ANZ,IND',
            'local_region'   => 'in:East,West',
            'stats_email'    => 'email',
            'sheet_filename' => 'string|max:255',
            'sheet_version'  => 'string|max:255|required_with:sheet_filename',
            'active'         => 'boolean',
        ];
    }

}
