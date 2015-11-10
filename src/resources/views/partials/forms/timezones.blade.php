{!! Form::select('timezone',
   isset($timezones)
       ? $timezones
       : DateTimeZone::listIdentifiers(),
   isset($selectedTimezone) ? $selectedTimezone : null,
   ['class' => 'form-control', 'onchange' => (isset($autoSubmit) && $autoSubmit) ? 'this.form.submit()' : '']) !!}
