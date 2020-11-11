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
Route::post('/employee/get-grr-card-template', 'EmployeeController@ajaxGetGrrCardTemplate');
Route::post('/employee/save-id-card', 'EmployeeController@ajaxSaveIdCard');
Route::post('/employee/get-id-card', 'EmployeeController@ajaxGetIdCard');
Route::post('/employee/save-changes', 'EmployeeController@ajaxSaveChanges');
Route::get('/employee/custom-id-card', 'EmployeeController@customIdCard');
Route::post('/employee/get-card-templates', 'EmployeeController@ajaxGetCardTemplates');


// Dashboard
Route::get('/dashboard', 'DashboardController@index');
Route::get('/dashboard/get-dashboard-info', 'DashboardController@ajaxGetDashboardInfo');
