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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/', 'eventsController@index');
Route::get('/mems', 'eventsController@showMems');
Route::post('/register', 'registerController@register');
Route::post('/inv/{e_id}', 'eventsController@invite');
Route::post('/create', 'eventsController@create');
Route::get('/show', 'eventsController@show');
Route::patch('/accept/{e_id}', 'eventscontroller@accept');//for both accepting and rejecting
Route::put('/update/{event_creator}', 'eventsController@update');
Route::delete('/delete/{event_creator}', 'eventscontroller@destroy');
Route::delete('/remove/{id}', 'eventscontroller@remove');
