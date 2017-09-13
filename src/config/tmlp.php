<?php

return [
    'admin_email' => env('ADMIN_EMAIL'),

    'api_use_cache' => env('API_USE_CACHE', false),
    'reports_use_cache' => env('REPORTS_USE_CACHE', true),

    'global_report_view_mode' => env('GLOBAL_REPORT_VIEW_MODE', 'react'),
    'local_report_view_mode' => env('LOCAL_REPORT_VIEW_MODE', 'html'),

    'earliest_submission' => '2017-06-02',
];
