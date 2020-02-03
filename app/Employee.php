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

    public function getGeneralManagers()
    {
        $data = DB::table('employees')
            ->where('is_manager', '=', 1)
            ->get()
            ->toArray();
        return $data;
    }

    public function getGeneralWorkers()
    {
        $data = DB::table('employees')
            ->where('is_manager', '=', 0)
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
}
