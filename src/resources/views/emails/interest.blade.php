{{ $interest_form->firstname }} {{ $interest_form->firstname }} is interested
in joining the {{ $interest_form->vision ? ' Vision ' : "" }}
{{ $interest_form->vision && $interest_form->regional ? " and " : "Team" }}
{{$interest_form->regional ? " Regional Statistician Team " : "" }}
<br>
<b>Summary</b>
<br>
{{ $interest_form->vision }} {{ $interest_form->regional }}
<br>
{{ $interest_form->firstname }} {{ $interest_form->firstname }}
<br>
{{ $interest_form->email }}
<br>
{{ $interest_form->phone }}
<br>
{{ $interest_form->team }}
<br>
<br>

Best,
The Vision Team
