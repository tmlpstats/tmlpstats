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
use TmlpStats\GlobalReport;
use TmlpStats\ReportToken;
use TmlpStats\User;

Route::match(['get', 'post'], 'admin/dashboard', 'AdminController@index');
Route::get('admin/status', 'AdminController@status');
Route::get('admin/peoplereport', 'AdminController@getPeopleReport');

Route::get('admin/import', 'ImportController@import');
Route::post('admin/import', 'ImportController@uploadImportSpreadsheet');

Route::resource('admin/centers', 'AdminCenterController');
Route::resource('admin/users', 'UserController');

// Stats Reports
Route::resource('statsreports', 'StatsReportController');
Route::post('statsreports/{id}/submit', 'StatsReportController@submit');
Route::get('statsreports/{id}/download', 'StatsReportController@downloadSheet');
Route::get('statsreports/{id}/{report}', 'StatsReportController@runDispatcher');

// Global Reports
Route::resource('globalreports', 'GlobalReportController');
Route::get('globalreports/{id}/{report}', 'GlobalReportController@runDispatcher');


Route::get('report/{token}', function ($token) {

    $reportToken = ReportToken::token($token)->first();
    if (!$reportToken) {
        abort(404);
    }

    Session::set('reportTokenId', $reportToken->id);

    $reportUrl = $reportToken->getReportPath();

    if ($reportUrl) {
        return redirect($reportUrl);
    } else {
        abort(404);
    }
});

// Center Info
Route::resource('center', 'CenterController');

// Import
Route::get('import', 'ImportController@index');
Route::post('import', 'ImportController@uploadSpreadsheet');

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

Route::get('auth/reauth', function() {
    // Logout and show login page (used to upgrade a report user)
    Auth::logout();

    return redirect('auth/login');
});

Route::controllers([
    'auth' => 'Auth\AuthController',
    'password' => 'Auth\PasswordController',
]);
