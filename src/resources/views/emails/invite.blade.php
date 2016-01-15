Hi {{ $invite->firstName }},

{{ $invite->invitedByUser->firstName }} {{ $invite->invitedByUser->lastName }} invited you to join the TMLP Stats community for team {{ $invite->center->name }} online.<br>
<br>
Please click the following link to register:<br>
<a href="{{ $acceptUrl }}" target="_blank">{{ $acceptUrl }}</a><br>
<br>
Best,
The TMLP Stats Team
