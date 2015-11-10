 {!! Form::select('region',
    (isset($includeLocalRegions) && $includeLocalRegions)
        ? [
            'ANZ' => 'Australia/New Zealand',
            'EME' => 'Europe/Middle East',
            'IND' => 'India',
            'NA'  => 'North America',
            'East'  => 'North America - East',
            'West'  => 'North America - West',
        ]
        : [
            'ANZ' => 'Australia/New Zealand',
            'EME' => 'Europe/Middle East',
            'IND' => 'India',
            'NA'  => 'North America',
        ], isset($selectedRegion) ? $selectedRegion : null,
    ['class' => 'form-control',  'onchange' => (isset($autoSubmit) && $autoSubmit) ? 'this.form.submit()' : '']) !!}
