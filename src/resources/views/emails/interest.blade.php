{{ $interest_form->firstname }} {{ $interest_form->lastname }} is interested
in joining the {{ $interest_form->vision ? ' Vision ' : "" }}
{{ $interest_form->vision_team && $interest_form->regional_team ? " and " : "Team" }}
{{$interest_form->regional ? " Regional Statistician Team " : "" }}
<br>
<br>
<h3>Summary</h3>
<br>
<h4>{{ $interest_form->firstname }} {{ $interest_form->lastname }}</h4>
<br>
{{ $interest_form->email }}
<br>
{{ $interest_form->phone }}
<br>
{{ $interest_form->team }}
<br>
<br>
Best,
<br>
The Vision Team
