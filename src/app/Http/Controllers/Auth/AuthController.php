<?php
namespace TmlpStats\Http\Controllers\Auth;

use Auth;
use Lang;
use TmlpStats\Person;
use TmlpStats\User;
use Validator;
use TmlpStats\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Create a new authentication controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data)
    {
        $messages = [
            'password.regex' => 'The password must contain at least one upper case letter, one lower case letter, and one number.',
        ];

        return Validator::make($data, [
            'first_name'  => 'required|max:255',
            'last_name'   => 'required|max:255',
            'phone'       => 'regex:/^[\s\d\+\-\.]+$/',
            'email'       => 'required|email|max:255|unique:users',
            'password'    => 'required|confirmed|min:8|max:4096|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'invite_code' => 'required|in:GloabalStatisticiansRock2015,GlobalStatisticiansRock2015',
        ], $messages);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return \TmlpStats\User
     */
    public function create(array $data)
    {
        $person = Person::firstOrCreate([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
        ]);
        if (isset($data['phone'])) {
            $person->phone = $data['phone'];
            $person->save();
        }

        return User::create([
            'person_id' => $person->id,
            'email'     => $data['email'],
            'password'  => bcrypt($data['password']),
        ]);
    }

    protected function authenticated(Request $request, User $user)
    {
        if ($user->active) {
            return redirect()->intended($this->redirectPath());
        }

        // Don't allow inactive users to login
        Auth::logout();

        return redirect($this->loginPath())
            ->withInput($request->only($this->loginUsername(), 'remember'))
            ->withErrors([
                $this->loginUsername() => Lang::get('auth.inactive'),
            ]);
    }
}
