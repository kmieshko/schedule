<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Schedule;
use App\Employee;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function index()
    {
//        DB::enableQueryLog();

        $schedules = Schedule::orderBy('id', 'desc')->first();
        if (empty($schedules)) {
            $schedule = $this->createScheduleFirstTime();
        } else {
            $schedule = $this->createSchedule($schedules);
        }


//        $query = DB::getQueryLog();
//        $query = end($query);

        echo '<pre>' . print_r($schedule, true) . '</pre>';
//        echo '<pre>' . print_r($query, true) . '</pre>';
        die;
        
    }

    protected function createScheduleFirstTime()
    {
        DB::enableQueryLog();

        $objEmployee = new Employee();
        $count_managers = $objEmployee->getCountManagers();
        $general_employees = $objEmployee->getGeneralEmployees();
        $weekends = $objEmployee->getWeekendsByPrioity();

        $managers_priorities = array();
        $employees = array();
        foreach ($general_employees as $employee) {
            $employees[$employee->id] = (array)$employee;
            if (!empty($employee->is_manager)) {
                $managers_priorities[] = $employee->id;
            }
        }
        // key (who first in queue) => nb_manager
        shuffle($managers_priorities);
        foreach ($weekends as $weekend) {
            if (!empty($managers_priorities)) {
                $employee_id = array_shift($managers_priorities);
                $employees[$employee_id]['weekend'] = $weekend->day;
            }
        }
        
        echo '<pre>' . print_r($employees, true) . '</pre>';
        die;
        
        $query = DB::getQueryLog();
        $query = end($query);

        $group_employees = array();
        foreach ($employees as $employee) {
            $group_employees[$employee->nb_team][$employee->id] = (array)$employee;
        }

        echo '<pre>' . print_r($group_employees, true) . '</pre>';
        echo '<pre>' . print_r($general_employees, true) . '</pre>';
        die;
        

    }

    protected function createSchedule($schedules)
    {
//        $count_managers = count(User::where('is_manager', 1)->toArray());
    }
}
