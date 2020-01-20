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
        $objSchedule = new Schedule();
        $schedules = $objSchedule->getLastSchedule();
        if (empty($schedules)) {
            $schedule = $this->createScheduleFirstTime();
        } else {
            $schedule = $this->createSchedule($schedules);
        }
        $objSchedule->insertSchedule($schedule);



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
        $managers = $objEmployee->getManagers();
        $workers = $objEmployee->getWorkers();
        $count_managers = count($managers);
        $count_workers = count($workers);
        $general_employees = $objEmployee->getGeneralEmployees();
        $weekends = $objEmployee->getWeekendsByPrioity();

        // weekends: key = priority day; value = weekend data
        foreach ($weekends as $key => $weekend) {
            $weekends[$weekend->priority] = $weekend;
        }
        unset($weekends[0]);

        // managers_priorities: id => [nb_week => priority_weekend_day]
        $managers_priorities = array();
        for ($i = 0; $i < $count_managers; $i++) {
            for ($j = 1; $j <= $count_managers; $j++) {
                $managers_priorities[$managers[$i]->id][$j] = ($i + $j) % $count_managers + 1;
            }
        }

        // workers have the same priorities like managers
        $data = array();
        for ($w = 1; $w <= $count_managers; $w++) {
            foreach ($general_employees as $employee) {
                if (!empty($employee->is_manager)) {
                    $id = $employee->id;
                }
                $week = 'W' . $w;
                $team = 'T' . $employee->nb_team;
                $weekend_day = $weekends[$managers_priorities[$id][$w]]->day;
                $data[$week][$team][$employee->id] = (array)$employee;
                $data[$week][$team][$employee->id]['weekend'] = $weekend_day;
            }
        }
        echo '<pre>' . print_r($data, true) . '</pre>';
        die;
        
        return $data;
    }

    protected function createSchedule($schedules)
    {
//        $count_managers = count(User::where('is_manager', 1)->toArray());
    }
}
