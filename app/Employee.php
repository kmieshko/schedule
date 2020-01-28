<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
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

    public function getNotGeneralEmployees()
    {
        $data = DB::table('employees')
            ->where('id_department', '!=', 1)
            ->get()
            ->toArray();
        return $data;
    }

    public function getWeekendsByPriority()
    {
        $data = DB::table('weekends')
            ->orderBy('priority', 'asc')
            ->get()
            ->toArray();
        return $data;
    }

    public function getWeekends()
    {
        $data = DB::table('weekends')
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();
        return $data;
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
        $data = DB::table('schedules')->where([
			['id_employee', '=', $id_employee],
			['id_week', '=', $id_week],
			])->get()
			->toArray();
		return $data;
	}

	public function checkWeekendByDayForGeneral($nb_week, $day)
	{
        $data = DB::table('schedules')->where([
			['id_week', '=', $nb_week],
            ['id_department', '=', 1],
			[$day, '=', 1],
			])->get()
			->toArray();
		return $data;
	}

    public function checkWeekendByDayForNotGeneral($nb_week, $day)
    {
        $data = DB::table('schedules')->where([
            ['id_week', '=', $nb_week],
            ['id_department', '!=', 1],
            [$day, '=', 1],
        ])->get()
            ->toArray();
        return $data;
    }

	public function checkWeekendByTeam($nb_week, $nb_team)
	{
        $data = DB::table('schedules')->where([
			['id_week', '=', $nb_week],
			['nb_team', '=', $nb_team],
			])
			->get()
			->toArray();
		if (!empty($data)) {
            $data = $data[0];
		}
		return $data;
	}
}
