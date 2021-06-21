<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('home','HomeController@index');
Route::post('home/list','HomeController@lists');
Route::get('home/test','HomeController@test');
Route::any('get','IndexController@get');
Route::post('getIntergration','IndexController@getIntergration');
Route::post('intergration','IndexController@Intergration');

Route::post('sitemap','IndexController@sitemap');
Route::any('lol/team_info','HomeController@teamInfo');
Route::any('lol/refresh','lolIndexController@refresh');
Route::any('refresh','IndexController@refresh');
Route::any('truncate','IndexController@truncate');
Route::any('submit','IndexController@submit');
