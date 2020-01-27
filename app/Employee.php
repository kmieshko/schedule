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

    public function getWeekendsByPriority()
    {
        $weekends = DB::table('weekends')
            ->orderBy('priority', 'asc')
            ->get()
            ->toArray();
        return $weekends;
    }

    public function getWeekends()
    {
        $weekends = DB::table('weekends')
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();
        return $weekends;
    }

    public function insertWeekend($id_employee, $nb_team, $id_department, $id_week, $day)
	{
		$data = array(
			'id_week' => $id_week,
			'id_employee' => $id_employee,
			'nb_team' => $nb_team,
			'id_department' => $id_department,
			'monday' => $day == 'monday' ? 1 : 0,
			'tuesday' => $day == 'tuesday' ? 1 : 0,
			'wednesday' => $day == 'wednesday' ? 1 : 0,
			'thursday' => $day == 'thursday' ? 1 : 0,
			'friday' => $day == 'friday' ? 1 : 0,
			'saturday' => $day == 'saturday' ? 1 : 0,
			'is_done' => 1,

		);
		DB::table('schedules')->insert($data);
	}

	public function checkWeekend($id_employee, $id_week)
	{
		$schedule = DB::table('schedules')->where([
			['id_employee', '=', $id_employee],
			['id_week', '=', $id_week],
			])->get()
			->toArray();
		return $schedule;
	}

	public function checkWeekendByDay($nb_week, $day)
	{
		$schedule = DB::table('schedules')->where([
			['id_week', '=', $nb_week],
			[$day, '=', 1],
			])->get()
			->toArray();
		return $schedule;
	}

	public function checkWeekendByTeam($nb_week, $nb_team)
	{
		$schedule = DB::table('schedules')->where([
			['id_week', '=', $nb_week],
			['nb_team', '=', $nb_team],
			])
			->get()
			->toArray();
		if (!empty($schedule)) {
			$schedule = $schedule[0];
		}
		return $schedule;
	}
}
