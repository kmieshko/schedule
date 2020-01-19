<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
    public function getCountManagers()
    {
        $managers = DB::table('employees')->where('is_manager', '=', 1)->get()->toArray();
        $count_managers = count($managers);
        return $count_managers;
    }

    public function getGeneralEmployees()
    {
        $general_employees = DB::table('employees')->where('id_department', '=', 1)->get()->toArray();
        return $general_employees;
    }

    public function getWeekendsByPrioity()
    {
        $weekends = DB::table('weekends')->orderBy('priority', 'asc')->get()->toArray();
        return $weekends;
    }
}
