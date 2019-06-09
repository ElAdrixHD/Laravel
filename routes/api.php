<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', 'PassportController@login');
//ruta: api/login
Route::post('register', 'PassportController@register');
//ruta: api/register

Route::middleware('auth:api')->group(function () {
    Route::get('user', 'PassportController@getDetails');
    //ruta: api/user
    Route::resource('reservas', 'ApiReservasController');
    //ruta: api/sites
    Route::get('logout', 'PassportController@logout');
    //ruta: api/logout
});
