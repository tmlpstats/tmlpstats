{{ $interest_form->firstname }} {{ $interest_form->lastname }} is interested
in joining the {{ $interest_form->vision_team ? " Vision " : "" }}
{{ ($interest_form->vision_team && $interest_form->regional_statistician_team) ? " and " : ($interest_form->regional_statistician_team ? "" :  "Team") }}
{{$interest_form->regional_statistician_team ? " Regional Statistician Team " : "" }}
<br>
<br>
<h3>[Interest Form]</h3>
<b>{{ $interest_form->firstname }} {{ $interest_form->lastname }}</b>
<br>
{{ $interest_form->email }}
<br>
{{ $interest_form->phone }}
<br>
{{ $interest_form->team->getGlobalRegion()->name }} - {{ $interest_form->team->name }}
<br>
<br>

- Vision Team
