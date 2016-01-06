<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
 */

// Admin Area

Route::post('api', 'ApiController@apiCall');

Route::match(['get', 'post'], 'admin/dashboard', 'AdminController@index');
Route::get('admin/status', 'AdminController@status');
Route::get('admin/peoplereport', 'AdminController@getPeopleReport');

Route::resource('admin/centers', 'AdminCenterController');
Route::post('admin/centers', 'AdminCenterController@batchUpdate');

Route::resource('admin/users', 'UserController');

// Invites
Route::resource('users/invites', 'InviteController');
Route::delete('users/invites/{id}/revoke', 'InviteController@revokeInvite');
Route::get('invites/{token}', 'InviteController@viewInvite');
Route::post('invites/{token}', 'InviteController@acceptInvite');

// Stats Reports
Route::resource('statsreports', 'StatsReportController');
Route::post('statsreports/{id}/submit', 'StatsReportController@submit');
Route::get('statsreports/{id}/download', 'StatsReportController@downloadSheet');
Route::get('statsreports/{id}/{report}', 'StatsReportController@dispatchReport');

// Global Reports
Route::resource('globalreports', 'GlobalReportController');
Route::get('globalreports/{id}/{report}', 'GlobalReportController@dispatchReport');

// Report Tokens
Route::resource('reporttokens', 'ReportTokenController');

// Reports
Route::get('report/{token}', 'ReportsController@getByToken');
Route::get('reports/centers/{abbr?}/{date?}', 'ReportsController@getCenterReport');
Route::get('reports/regions/{abbr?}/{date?}', 'ReportsController@getRegionReport');

Route::post('reports/centers/setActive', 'ReportsController@setCenter');
Route::post('reports/regions/setActive', 'ReportsController@setRegion');
Route::post('reports/dates/setActive', 'ReportsController@setReportingDate');
Route::get('m/{abbr}', 'ReportsController@mobileDash');

// Center Info
Route::resource('centers', 'CenterController');
Route::get('center/{abbr}', 'CenterController@dashboard');

// Regions
Route::resource('regions', 'RegionController');

// Validate
Route::get('validate', 'ImportController@indexValidateSheet');
Route::post('validate', 'ImportController@validateSheet');

// Import
Route::get('import', 'ImportController@indexImportSheet');
Route::post('import', 'ImportController@importSheet');

Route::match(['get', 'post'], 'home', 'HomeController@index');
Route::get('/', 'WelcomeController@index');

Route::post('home/clientsettings', function () {
    // Save the user's timezone
    if (Request::has('timezone')) {
        Session::put('timezone', Request::get('timezone'));
    }
    // Save the user's locale
    if (Request::has('locale')) {
        Session::put('locale', Request::get('locale'));
    }
});

Route::post('feedback', 'ContactController@processFeedback');

Route::controllers([
    'auth' => 'Auth\AuthController',
    'password' => 'Auth\PasswordController',
]);
