<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Schedule extends Model
{
	public function getAllSchedules()
	{
		$data = DB::table("schedules")
			->get()
			->toArray();
		return	$data;
	}

	public function getAllWeeksSchedules()
	{
		$data = DB::table("schedules")
			->select('id_week', 'week_start', 'week_end')
			->distinct()
			->get()
			->toArray();
		return	$data;
	}

    public function getLatestSchedule()
    {
		$data = DB::table("schedules")
            ->where('id_week', '=', DB::raw("(SELECT MAX(schedules.id_week) FROM schedules)"))
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

	public function insertWeekend($data)
	{
		$data = array(
			'id_week' => $data['id_week'],
			'id_employee' => $data['id_employee'],
			'nb_team' => $data['nb_team'],
			'id_department' => $data['id_department'],
			'monday' => $data['day'] == 'monday' ? 1 : 0,
			'tuesday' => $data['day'] == 'tuesday' ? 1 : 0,
			'wednesday' => $data['day'] == 'wednesday' ? 1 : 0,
			'thursday' => $data['day'] == 'thursday' ? 1 : 0,
			'friday' => $data['day'] == 'friday' ? 1 : 0,
			'saturday' => $data['day'] == 'saturday' ? 1 : 0,
			'week_start' => $data['week_start'],
			'week_end' => $data['week_end'],
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

	public function checkWeekendByDayForNonGeneral($nb_week, $day)
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
		$result = DB::table('schedules')->where([
			['id_week', '=', $nb_week],
			['nb_team', '=', $nb_team],
		])
            ->orderBy('id', 'desc')
            ->limit(1)
			->get()
			->toArray();
		return !empty($result) ? $result[0] : array();
	}

	public function getLatestDayForGeneral($latest_week)
	{
		$result = DB::table('schedules')
			->select('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday')
			->where('id_department', '=', 1)
			->where('id_week', '<=', $latest_week)
			->where('saturday', '=', 0)
			->orderBy('id', 'desc')
			->limit(1)
			->get()
			->toArray();
		return !empty($result) ? $result[0] : array();
	}

	public function getLatestDayForNonGeneral($latest_week)
	{
		$result = DB::table('schedules')
			->select('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday')
			->where('id_department', '!=', 1)
			->where('id_week', '<=', $latest_week)
            ->where('saturday', '=', 0)
			->orderBy('id', 'desc')
			->limit(1)
			->get()
			->toArray();
		return !empty($result) ? $result[0] : array();
	}

	public function getScheduleByWeek($id_week)
	{
		$result = DB::table('schedules')
			->join('employees', 'employees.id', '=', 'schedules.id_employee')
			->select('schedules.*', 'employees.first_name', 'employees.last_name')
			->where('id_week', '=', $id_week)
			->get()
			->toArray();
		return $result;
	}
}
