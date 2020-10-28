<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
	public function getAllEmployees()
	{
		$data = DB::table('employees')
			->get()
			->toArray();
		return $data;
	}

	public function getAllEmployeesWithDepartments()
    {
        $data = DB::table('employees')
            ->join('departments', 'employees.id_department', '=', 'departments.id')
            ->select('employees.*', 'departments.name as department_name')
            ->orderBy('employees.nb_team', 'asc')
            ->orderBy('employees.id', 'asc')
            ->get()
            ->toArray();
        return $data;
    }

    public function getGeneralManagers()
    {
        $data = DB::table('employees')
            ->where('is_manager', '=', 1)
            ->where('id_department', '=', 1)
            ->get()
            ->toArray();
        return $data;
    }

    public function getGeneralWorkers()
    {
        $data = DB::table('employees')
            ->where('is_manager', '=', 0)
            ->where('id_department', '=', 1)
            ->get()
            ->toArray();
        return $data;
    }

    public function getGeneralEmployees()
    {
        $data = DB::table('employees')
            ->where('id_department', '=', 1)
            ->get()
            ->toArray();
        return $data;
    }

    public function getNonGeneralEmployees()
    {
        $data = DB::table('employees')
            ->where('id_department', '!=', 1)
            ->get()
            ->toArray();
        return $data;
    }

    public function getNonGeneralEmployeesExcluded($employees)
    {
        $data = DB::table('employees')
            ->where('id_department', '!=', 1)
            ->whereNotIn('id', $employees)
            ->get()
            ->toArray();
        return $data;
    }

    public function getGeneralWorkersExcluded($employees)
    {
        $data = DB::table('employees')
            ->where('is_manager', '=', 0)
            ->whereNotIn('id', $employees)
            ->get()
            ->toArray();
        return $data;
    }

    public function getGeneralManagersExcluded($employees)
    {
        $data = DB::table('employees')
            ->where('is_manager', '=', 1)
            ->whereNotIn('id', $employees)
            ->get()
            ->toArray();
        return $data;
    }

    public function deleteEmployee($id_employee)
    {
        DB::table('employees')
            ->where('id', '=', $id_employee)
            ->delete();
    }

    public function getEmployeeById($id_employee)
    {
        $data = DB::table('employees')
            ->where('id', '=', $id_employee)
            ->get()
            ->toArray();
        return !empty($data) ? (array)$data[0] : array();
    }

    public function getEmployeeByTeam($nb_team)
    {
        $data = DB::table('employees')
            ->where('nb_team', '=', $nb_team)
            ->get()
            ->toArray();
        return $data;
    }

    public function setAsManagerById($id_employee)
    {
        $data = DB::table('employees')
            ->where('id', '=', $id_employee)
            ->update(['is_manager' => 1]);
        return $data;
    }

    public function getDepartments()
    {
        $data = DB::table('departments')
            ->get()
            ->toArray();
        return $data;
    }

    public function createEmployee($data)
    {
        DB::table('employees')->insert($data);
    }
}
