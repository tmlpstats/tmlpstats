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
Route::filter('globalStatistician', function()
{
    if (!Auth::check()) {
        return redirect('auth/login');
    }
    if (!Auth::user()->hasRole('globalStatistician') && !Auth::user()->hasRole('administrator')) {
        return App::abort(403, 'Unauthorized action.');
    }
});

// Admin Area
Route::when('admin/*', 'auth|admin');

Route::get('admin/dashboard', 'AdminController@index');

Route::get('admin/import', 'ImportController@import');
Route::post('admin/import', 'ImportController@uploadImportSpreadsheet');

// Route::get('admin/centers/import', 'CenterController@import');
// Route::get('admin/quarters/import', 'QuarterController@import');
// Route::get('admin/roles/import', 'RoleController@import');

Route::resource('admin/centers', 'CenterController');
Route::resource('admin/quarters', 'QuarterController');
Route::resource('admin/users', 'UserController');
Route::resource('admin/roles', 'RoleController');

// Import
Route::when('import', 'auth|globalStatistician');

Route::get('import', 'ImportController@index');
Route::post('import', 'ImportController@uploadSpreadsheet');

Route::get('home', 'HomeController@index');
Route::get('/', 'WelcomeController@index');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
