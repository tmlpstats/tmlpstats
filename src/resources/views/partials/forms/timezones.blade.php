<?php
    $name = isset($elementName) ? $elementName : 'timezone';
    $timezones = isset($timezones) ? $timezones : DateTimeZone::listIdentifiers();
    $selected = isset($selectedTimezone) ? $selectedTimezone : null;
?>
{!! Form::select($name, $timezones, $selected,
   ['class' => 'form-control', 'onchange' => (isset($autoSubmit) && $autoSubmit) ? 'this.form.submit()' : '']) !!}
