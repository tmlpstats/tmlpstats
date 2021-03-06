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
Route::post('api/{method}', 'ApiController@apiCall');

Route::group(['prefix' => 'admin'], function () {
    Route::resource('centers', 'AdminCenterController');
    Route::post('centers', 'AdminCenterController@batchUpdate');

    Route::resource('users', 'UserController');

    Route::get('regions/{regionAbbr}/{page?}/{tab1?}/{tab2?}/{irrelevant3?}/{irrelevant4?}', 'ManageController@region');
    Route::get('system/{ignore?}/{ignore2?}', 'ManageController@system');
});

// Invites
Route::resource('users/invites', 'InviteController');
Route::delete('users/invites/{id}/revoke', 'InviteController@revokeInvite');
Route::get('invites/{token}', 'InviteController@viewInvite');
Route::post('invites/{token}', 'InviteController@acceptInvite');

// Stats Reports
Route::get('statsreports/{id}/{report}', 'StatsReportController@dispatchReport');
Route::get('statsreports/{id}', 'StatsReportController@show');

// Global Reports
Route::get('globalreports', 'GlobalReportController@index');
Route::get('globalreports/{id}', 'GlobalReportController@show');
Route::get('globalreports/{id}/{report}/{regionAbbr?}', 'GlobalReportController@dispatchReport');

// Reports
Route::get('report/{token}', 'ReportsController@getByToken');
Route::get('reports/centers/{abbr?}/{date?}/{tab1?}/{tab2?}', 'ReportsController@getCenterReport');
Route::get('reports/regions/{abbr?}/{date?}/{tab1?}/{tab2?}', 'ReportsController@getRegionReport');
Route::post('reports/centers/setActive', 'ReportsController@setActiveCenter');
Route::post('reports/regions/setActive', 'ReportsController@setActiveRegion');
Route::post('reports/dates/setActive', 'ReportsController@setActiveReportingDate');

Route::get('m/{abbr}', 'ReportsController@mobileDash');

// Downloads
Route::get('downloads/regions/{abbr}/{date}/{report}', 'GlobalReportController@generateApplicationsOverdueCsv');

// Center Info
Route::get('center/{abbr}', 'CenterController@show');
Route::get('center/{abbr}/next_qtr_accountabilities', 'CenterController@nextQtrAccountabilities');
Route::get('center/{abbr}/submission/{reportingDate?}/{page?}/{irrelevant?}/{irrelevant2?}', 'CenterController@submission');

// Regions
Route::get('regions', 'RegionController@index');
Route::get('regions/{id}', 'RegionController@show');

Route::match(['get', 'post'], 'home', 'HomeController@index');
Route::match(['get', 'post'], 'home/{abbr}', 'HomeController@home');

Route::get('/', 'WelcomeController@index');
Route::get('apply', 'WelcomeController@apply');
Route::get('apply/regionalstatistican', 'WelcomeController@applyRegionalStatistician');


Route::get('interest', 'InterestFormController@index');
Route::post('interest', 'InterestFormController@submit');

Route::post('feedback', 'ContactController@processFeedback');

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// Password reset link request routes...
Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');

// Password reset routes...
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');

// Help
Route::get('about', 'HelpController@about');
Route::get('docs', 'HelpController@docs');
Route::get('help', 'HelpController@index');
Route::get('help/view/{id}', 'HelpController@view');
