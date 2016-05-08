<?php
    $elementName = isset($elementName) ? $elementName : 'timezone';
    $timezones = isset($timezones) ? $timezones : DateTimeZone::listIdentifiers();
    $selectedTimezone = isset($selectedTimezone) ? $selectedTimezone : null;
?>
{!! Form::select($elementName, $timezones, $selectedTimezone,
   ['class' => 'form-control', 'onchange' => (isset($autoSubmit) && $autoSubmit) ? 'this.form.submit()' : '']) !!}
