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
Route::get('globalreports/{id}/{report}/{regionAbbr?}', 'GlobalReportController@dispatchReport');

// Report Tokens
Route::resource('reporttokens', 'ReportTokenController');

// Reports
Route::get('report/{token}', 'ReportsController@getByToken');
Route::get('reports/centers/{abbr?}/{date?}', 'ReportsController@getCenterReport');
Route::get('reports/regions/{abbr?}/{date?}', 'ReportsController@getRegionReport');

Route::post('reports/centers/setActive', 'ReportsController@setActiveCenter');
Route::post('reports/regions/setActive', 'ReportsController@setActiveRegion');
Route::post('reports/dates/setActive', 'ReportsController@setActiveReportingDate');
Route::get('m/{abbr}', 'ReportsController@mobileDash');

// Center Info
Route::get('center/{abbr}', 'CenterController@dashboard');
Route::get('center/{abbr}/submission/{page?}', 'CenterController@submission');

// Regions
Route::resource('regions', 'RegionController');

// Validate
Route::get('validate', 'ImportController@indexValidateSheet')->name('validate');
Route::post('validate', 'ImportController@validateSheet');

// Import
Route::get('import', 'ImportController@indexImportSheet');
Route::post('import', 'ImportController@importSheet');

Route::match(['get', 'post'], 'home', 'HomeController@index');
Route::match(['get', 'post'], 'home/{abbr}/{date?}', 'HomeController@home');

Route::get('/', 'WelcomeController@index');

Route::post('feedback', 'ContactController@processFeedback');

Route::controllers([
    'auth' => 'Auth\AuthController',
    'password' => 'Auth\PasswordController',
]);
