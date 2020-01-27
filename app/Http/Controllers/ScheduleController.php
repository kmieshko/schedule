<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Schedule;
use App\Employee;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Debug\Debug;

class ScheduleController extends Controller
{
	public function index()
	{
//	  DB::enableQueryLog();
		$objSchedule = new Schedule();
		$schedules = $objSchedule->getLastSchedule();
//		if (empty($schedules)) {
//		  $schedule = $this->createScheduleFirstTime();
			$schedule = $this->createScheduleFirstTime2();
//		} else {
//			$schedule = $this->createSchedule($schedules);
//		}
//		$objSchedule->insertSchedule($schedule);



//	  $query = DB::getQueryLog();
//	  $query = end($query);

		echo '<pre>' . print_r($schedule, true) . '</pre>';
//	  echo '<pre>' . print_r($query, true) . '</pre>';
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
		$weekends = $objEmployee->getWeekendsByPriority();

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

	protected function createScheduleFirstTime2()
	{
		DB::enableQueryLog();

		$objEmployee = new Employee();
		$managers = $objEmployee->getManagers();
		$workers = $objEmployee->getWorkers();
		$count_managers = count($managers);
		$count_workers = count($workers);
		$general_employees = $objEmployee->getGeneralEmployees();
		$tmp_weekends = $objEmployee->getWeekends();
		$saturday = array_pop($tmp_weekends);

		foreach ($tmp_weekends as $weekend) {
			$weekends[] = strtolower($weekend->day);
		}

		$data = array();
		$employees = $this->sortEmployees($managers, $workers);
		foreach (range(1, 19, 1) as $nb_week) {
			foreach ($employees as $teams) {
				$i = 0;
				foreach ($teams as $team => $employee) {
					$day = current($weekends);
					$check_weekend = $objEmployee->checkWeekend($employee['id'], $nb_week); // есть ли выхоной
					if (empty($check_weekend)) {
						$check_saturday = $objEmployee->checkWeekendByDay($nb_week, 'saturday');
						if (!$check_saturday) { // если сб не записана
							$objEmployee->insertWeekend($employee['id'], $employee['nb_team'], $nb_week, 'saturday');
						} else {
							$teammate = $objEmployee->checkWeekendByTeam($nb_week, $employee['nb_team']);
							if (!empty($teammate) && $teammate->saturday == 1 && $teammate->id_employee != $employee['id']) { // если у напарника по команде стоит выходной в сб - проставить сб
								$objEmployee->insertWeekend($employee['id'], $employee['nb_team'], $nb_week, 'saturday');
							} else {
								$objEmployee->insertWeekend($employee['id'], $employee['nb_team'], $nb_week, $day);
								$day == 'friday' ? reset($weekends) : next($weekends);
							}
						}
					}
				}
			}
		}
		echo '<pre>' . print_r($data, true) . '</pre>';
		die;

		return $data;
	}

	public function sortEmployees($managers, $workers)
	{
		$result = array();
		foreach ($managers as $manager) {
			$result['T' . $manager->nb_team][$manager->id] = (array)$manager;
			foreach ($workers as $worker) {
				if ($manager->nb_team == $worker->nb_team) {
					$result['T' . $worker->nb_team][$worker->id] = (array)$worker;
				}
			}
		}
		return $result;
	}

	protected function createSchedule($schedules)
	{
//	  $count_managers = count(User::where('is_manager', 1)->toArray());
	}
}
