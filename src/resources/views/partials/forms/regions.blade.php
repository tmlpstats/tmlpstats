 {!! Form::select('region',
    (isset($includeLocalRegions) && $includeLocalRegions)
        ? [
            'ANZ' => 'Australia/New Zealand',
            'EME' => 'Europe/Middle East',
            'IND' => 'India',
            'NA'  => 'North America',
            'EAST'  => 'North America - East',
            'WEST'  => 'North America - West',
            'MUM' => 'Mumbai Centre',
            'BLR' => 'Bangalore Centre',
            'DL' => 'Delhi Centre'
        ]
        : [
            'ANZ' => 'Australia/New Zealand',
            'EME' => 'Europe/Middle East',
            'IND' => 'India',
            'NA'  => 'North America',
        ], isset($selectedRegion) ? $selectedRegion : null,
    ['class' => 'form-control',  'onchange' => (isset($autoSubmit) && $autoSubmit) ? 'this.form.submit()' : '']) !!}
