<?php

use TmlpStats\Import\ImportManager;

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
Route::resource('admin/quarters', 'QuarterController');
Route::resource('admin/users', 'UserController');
Route::resource('admin/roles', 'RoleController');

Route::resource('statsreports', 'StatsReportController');
Route::resource('globalreports', 'GlobalReportController');

// Import
Route::when('import', 'auth|statistician');

Route::get('import', 'ImportController@index');
Route::post('import', 'ImportController@uploadSpreadsheet');

Route::get('download/sheets/{date}/{center}', array('as' => 'downloadSheet', 'uses' => function($date, $center) {
    $path = ImportManager::getSheetPath($date, $center);
    if ($path)
    {
        return Response::download($path, basename($path), [
            'Content-Length: '. filesize($path)
        ]);
    }
    else
    {
        abort(404);
    }
}))
->where('date', '^\d\d\d\d-\d\d-\d\d$')
->where('center', '^\w+$');

Route::match(['get', 'post'], 'home', 'HomeController@index');
Route::post('home/timezone', 'HomeController@setTimezone');
Route::get('/', 'WelcomeController@index');

Route::controllers([
    'auth' => 'Auth\AuthController',
    'password' => 'Auth\PasswordController',
]);
