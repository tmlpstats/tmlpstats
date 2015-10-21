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

Route::filter('admin', function()
{
    if (!Auth::check()) {
        return redirect('auth/login');
    }
    if (!Auth::user()->hasRole('administrator')) {
        return App::abort(403, 'Unauthorized action.');
    }
});
Route::filter('statistician', function()
{
    if (!Auth::check()) {
        return redirect('auth/login');
    }
    if (!Auth::user()->hasRole('localStatistician') && !Auth::user()->hasRole('globalStatistician') && !Auth::user()->hasRole('administrator')) {
        return App::abort(403, 'Unauthorized action.');
    }
});

// Admin Area
Route::when('admin/*', 'auth|admin');

Route::match(['get', 'post'], 'admin/dashboard', 'AdminController@index');

Route::get('admin/import', 'ImportController@import');
Route::post('admin/import', 'ImportController@uploadImportSpreadsheet');

Route::resource('admin/centers', 'CenterController');
//Route::resource('admin/quarters', 'QuarterController');
Route::resource('admin/users', 'UserController');
Route::resource('admin/roles', 'RoleController');

// User Profiles
//Route::get('profile', 'UserControler@showProfile');
//Route::post('profile', 'UserControler@updateProfile');

// Stats Reports
Route::resource('statsreports', 'StatsReportController');
Route::get('statsreports/{id}/download', 'StatsReportController@downloadSheet');
Route::post('statsreports/{id}/submit', 'StatsReportController@submit');

Route::get('statsreports/{id}/summary', 'StatsReportController@getSummary');
Route::get('statsreports/{id}/results', 'StatsReportController@getResults');
Route::get('statsreports/{id}/centerstats', 'StatsReportController@getCenterStats');
Route::get('statsreports/{id}/classlist', 'StatsReportController@getTeamMembers');
Route::get('statsreports/{id}/tmlpregistrations', 'StatsReportController@getTmlpRegistrations');
Route::get('statsreports/{id}/courses', 'StatsReportController@getCourses');
Route::get('statsreports/{id}/contactinfo', 'StatsReportController@getContacts');


Route::resource('globalreports', 'GlobalReportController');

// Import
Route::when('import', 'auth|statistician');

Route::get('import', 'ImportController@index');
Route::post('import', 'ImportController@uploadSpreadsheet');

Route::match(['get', 'post'], 'home', 'HomeController@index');
Route::post('home/timezone', 'HomeController@setTimezone');
Route::get('/', 'WelcomeController@index');

Route::controllers([
    'auth' => 'Auth\AuthController',
    'password' => 'Auth\PasswordController',
]);
