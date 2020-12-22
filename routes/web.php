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
//分组案例
/**
Route::group(['middleware' => ['auth' => 'admin.permission'], 'prefix' => 'monitor', 'namespace' => 'Monitor', 'as' => 'monitor.'], function () {
    Route::get('/workflow-task/list', 'WorkflowTaskController@list')->name('workflow-task.list');
    Route::post('/workflow-task/redo/{id}', 'WorkflowTaskController@redo')->name('workflow-task.redo');
    Route::post('/workflow-task/mark/{id}', 'WorkflowTaskController@mark')->name('workflow-task.mark');
    Route::get('/queue/list', 'QueueController@list')->name('queue.list');
    Route::get('/queue/jobs', 'QueueController@jobs')->name('queue.jobs');
    Route::post('/queue/retry/{id}', 'QueueController@retry')->name('queue.retry');
    Route::post('/queue/delete/{id}', 'QueueController@delete')->name('queue.delete');
});*/
