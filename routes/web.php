<?php

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

// Schedule
Route::get('/schedules', 'ScheduleController@index2');
Route::post('/schedules/schedule-by-week', 'ScheduleController@ajaxScheduleByWeek');
Route::get('/schedules/create', 'ScheduleController@create');
Route::post('/schedules/create-schedule', 'ScheduleController@ajaxCreateSchedule');
Route::get('/schedules/download-schedule', 'ScheduleController@ajaxDownloadSchedule');
Route::post('/schedules/save-changes', 'ScheduleController@ajaxSaveChanges');

// Employee
Route::get('/employee', 'EmployeeController@index');
Route::post('/employee/delete-employee', 'EmployeeController@ajaxDeleteEmployee');
Route::get('/employee/create', 'EmployeeController@create');
Route::post('/employee/create-employee', 'EmployeeController@ajaxCreateEmployee');
