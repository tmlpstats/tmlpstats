<?php

namespace TmlpStats\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use Mail;
use Session;
use TmlpStats\Center;
use TmlpStats\Invite;
use TmlpStats\Person;
use TmlpStats\Role;
use TmlpStats\User;
use TmlpStats\Util;

class InviteController extends Controller
{
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
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('index', Invite::class);

        $invites = Invite::all();

        return view('invites.index', compact('invites'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', Invite::class);

        $rolesObjects = Role::all();

        $selectedRole = null;
        $roles        = [];
        foreach ($rolesObjects as $role) {
            $roles[$role->id] = $role->display;

            if ($role->name == 'readonly') {
                $selectedRole = $role->id;
            }
        }

        $centerList = Center::active()->get();
        $centers    = [];
        foreach ($centerList as $center) {
            $region                         = $center->region->getParentGlobalRegion();
            $centers[$center->abbreviation] = "{$region->name} - {$center->name}";
        }
        asort($centers);

        $centers = ['default' => 'Select a Center'] + $centers;

        return view('invites.create', compact('centers', 'roles', 'selectedRole'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', Invite::class);

        $invite = new Invite($request->all());

        if ($request->has('center')) {
            $center = Center::abbreviation($request->get('center'))->first();
            if ($center) {
                $invite->centerId = $center->id;
            }
        }

        if ($request->has('role')) {
            $role = Role::find($request->get('role'));
            if ($role) {
                $invite->roleId = $role->id;
            }
        }

        $invite->invitedByUserId = Auth::user()->id;
        $invite->token = Util::getRandomString();

        $invite->save();

        // TODO: display result messages
        $results = $this->sendInvite($invite);
        Session::flash('results', $results);

        return redirect("/users/invites/{$invite->id}");
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invite = Invite::findOrFail($id);

        $this->authorize($invite);

        return view('invites.show', compact('invite'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $invite = Invite::findOrFail($id);

        $this->authorize($invite);

        $rolesObjects = Role::all();

        $roles = [];
        foreach ($rolesObjects as $role) {
            $roles[$role->id] = $role->display;
        }

        $centerList = Center::active()->get();
        $centers    = [];
        foreach ($centerList as $center) {
            $region                         = $center->region->getParentGlobalRegion();
            $centers[$center->abbreviation] = "{$region->name} - {$center->name}";
        }
        asort($centers);

        $centers = ['default' => 'Select a Center'] + $centers;

        return view('invites.edit', compact('invite', 'roles', 'centers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $invite = Invite::findOrFail($id);

        $this->authorize($invite);

        $invite->update($request->all());

        if ($request->has('center')) {
            $center = Center::abbreviation($request->get('center'))->first();
            if ($center) {
                $invite->centerId = $center->id;
            }
        }

        if ($request->has('role')) {
            $role = Role::find($request->get('role'));
            if ($role) {
                $invite->roleId = $role->id;
            }
        }

        if ($invite->isDirty()) {
            $invite->save();
        }

        if ($request->has('resend_invite')) {
            // TODO: display result messages
            $results = $this->sendInvite($invite);
            Session::flash('results', $results);
        }

        $redirect = $request->has('previous_url')
            ? $request->get('previous_url')
            : "users/invites/{$id}";

        return redirect($redirect);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $invite = Invite::findOrFail($id);

        $this->authorize($invite);

        $invite->delete();

        $redirect = $request->has('previous_url')
            ? $request->get('previous_url')
            : 'users/invites';

        return redirect($redirect);
    }

    /**
     * Revoke an invite
     *
     * @param Request $request
     * @param         $id
     *
     * @return array
     */
    protected function revokeInvite(Request $request, $id)
    {
        $invite = Invite::findOrFail($id);

        $this->authorize($invite);

        $response = [
            'invite' => $id,
        ];
        if ($invite->delete()) {
            $response['success'] = true;
            $response['message'] = "Invitation for {$invite->firstName} revoked.";
        } else {
            $response['success'] = false;
            $response['message'] = 'Unable to revoke invitation. Please try again.';
        }

        return $response;
    }

    /**
     * Send the invitation email
     *
     * @param Invite $invite
     */
    protected function sendInvite(Invite $invite)
    {
        $acceptUrl = url("/invites/{$invite->token}");

        try {
            Mail::send('emails.invite', compact('invite', 'acceptUrl'),
                function($message) use ($invite) {
                    // Only send email to person in production
                    if (env('APP_ENV') === 'prod') {
                        $message->to($invite->email);
                    } else {
                        $message->to(env('ADMIN_EMAIL'));
                    }

                    $message->subject("Your TMLP Stats Account Invitation");
            });
            $name = $invite->firstName ?: $invite->email;
            $successMessage = "Success! Invitation email sent to {$name}.";
            if (env('APP_ENV') === 'prod') {
                Log::info("Invite email sent to {$invite->email} for invite {$invite->id}");
            } else {
                Log::info("Invite email sent to " . env('ADMIN_EMAIL') . " for invite {$invite->id}");
                $successMessage .= "<br/><br/><strong>Since this is development, we sent it to " . env('ADMIN_EMAIL') . " instead.</strong>";
            }
            $results['success'][] = $successMessage;

            $invite->emailSentAt = Carbon::now();
            $invite->save();
        } catch (\Exception $e) {
            Log::error("Exception caught sending invite email: " . $e->getMessage());
            $name = $invite->firstName ?: $invite->email;
            $results['error'][] = "Failed to send invitation email to {$name}. Please try again.";
        }

        return $results;
    }

    /**
     * Show invitation acceptance page
     *
     * @param $token Invite's token
     *
     * @return \Illuminate\View\View
     */
    public function viewInvite($token)
    {
        $invite = Invite::token($token)->first();
        if (!$invite) {
            abort(404);
        }

        return view('invites.accept', compact('invite'));
    }

    /**
     * Process invitation acceptance form
     *
     * @param Request $request
     * @param         $token
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function acceptInvite(Request $request, $token)
    {
        $invite = Invite::token($token)->first();
        if (!$invite) {
            abort(404);
        }

        $this->validate($request, [
            'first_name'  => 'required|max:255',
            'last_name'   => 'required|max:255',
            'phone'       => 'regex:/^[\s\d\+\-\.]+$/',
            'email'       => 'required|email|max:255|unique:users',
            'password'    => 'required|confirmed|min:8|max:4096|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        ], [
            'password.regex' => 'The password must contain at least one upper case letter, one lower case letter, and one number.',
        ]);


        $person = Person::firstOrCreate([
            'first_name' => $request->get('first_name'),
            'last_name'  => $request->get('last_name'),
            'email'      => $request->get('email'),
        ]);

        if ($request->has('phone')) {
            $person->phone = $request->get('phone');
        }

        $person->centerId = $invite->centerId;
        $person->save();

        $user = User::create([
            'person_id' => $person->id,
            'email'     => $request->get('email'),
            'password'  => bcrypt($request->get('password')),
            'role_id'   => $invite->roleId,
        ]);

        Log::info("User {$user->id} accepted invite {$invite->id}. Deleting invite.");
        $invite->delete();

        if (Auth::attempt(['email' => $request->get('email'), 'password' => $request->get('password')])) {
            return redirect('/');
        }

        return redirect('auth/login');
    }
}
