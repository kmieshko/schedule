<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
    public function getManagers()
    {
        $managers = DB::table('employees')
            ->where('is_manager', '=', 1)
            ->get()
            ->toArray();
        return $managers;
    }

    public function getWorkers()
    {
        $workers = DB::table('employees')
            ->where('is_manager', '=', 0)
            ->get()
            ->toArray();
        return $workers;
    }

    public function getGeneralEmployees()
    {
        $general_employees = DB::table('employees')
            ->where('id_department', '=', 1)
            ->get()
            ->toArray();
        return $general_employees;
    }

    public function getWeekendsByPrioity()
    {
        $weekends = DB::table('weekends')
            ->orderBy('priority', 'asc')
            ->get()
            ->toArray();
        return $weekends;
    }
}
