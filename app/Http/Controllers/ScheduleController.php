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
		$schedules = $objSchedule->getLatestSchedule();
		if (empty($schedules)) {
//		    $schedule = $this->createScheduleFirstTime();
		    $schedule = $this->createScheduleFirstTime2();
		} else {
			$schedule = $this->continueSchedule($schedules);
		}


//	  $query = DB::getQueryLog();
//	  $query = end($query);

		echo '<pre>' . print_r($schedule, true) . '</pre>';
//	  echo '<pre>' . print_r($query, true) . '</pre>';
		die;

	}

	public function createScheduleFirstTime()
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
		return $data;
	}

    public function sortGeneralEmployees($managers, $workers)
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

    public function sortNotGeneralEmployees($employees)
    {
        $result = array();
        foreach ($employees as $employee) {
            $result['T' . $employee->nb_team][$employee->id] = (array)$employee;
        }
        return $result;
    }

    public function createScheduleForGeneralEmployees($general_employees, $nb_week, &$weekends)
    {
        $objEmployee = new Employee();
        foreach ($general_employees as $teams) {
            foreach ($teams as $team => $employee) {
                $day = current($weekends);
                $check_weekend = $objEmployee->checkWeekend($employee['id'], $nb_week); // есть ли выхоной
                if (empty($check_weekend)) {
                    $check_saturday = $objEmployee->checkWeekendByDayForGeneral($nb_week, 'saturday');
                    if (!$check_saturday) { // if current Saturday is available
                        // check latest 2 team weeks
                        $last_saturday = $objEmployee->checkWeekendByTeam($nb_week - 1, $employee['nb_team']);
                        $before_last_saturday = $objEmployee->checkWeekendByTeam($nb_week - 2, $employee['nb_team']);
                        if ((empty($last_saturday) || !$last_saturday->saturday) &&
                            (empty($before_last_saturday) || !$before_last_saturday->saturday)) { // if team hadn't Saturday in latest 2 weeks - weekend is Saturday
                            $objEmployee->insertWeekend($employee['id'], $employee['nb_team'], $employee['id_department'], $nb_week, 'saturday');
                            $data['W' . $nb_week]['saturday'][$employee['id']] = $employee;
                        } else { // if team had Saturday in latest 2 weeks - weekend current weekend day
                            $objEmployee->insertWeekend($employee['id'], $employee['nb_team'], $employee['id_department'], $nb_week, $day);
                            $data['W' . $nb_week][$day][$employee['id']] = $employee;
                            $day == 'friday' ? reset($weekends) : next($weekends);
                        }
                    } else { // if Saturday isn't available
                        $teammate = $objEmployee->checkWeekendByTeam($nb_week, $employee['nb_team']);
                        if (!empty($teammate) && $teammate->saturday == 1 && $teammate->id_employee != $employee['id']) { // if teammate has Saturday weekend - Saturday is weekend for whole team
                            $objEmployee->insertWeekend($employee['id'], $employee['nb_team'], $employee['id_department'], $nb_week, 'saturday');
                            $data['W' . $nb_week]['saturday'][$employee['id']] = $employee;
                        } else {
                            $objEmployee->insertWeekend($employee['id'], $employee['nb_team'], $employee['id_department'], $nb_week, $day);
                            $data['W' . $nb_week][$day][$employee['id']] = $employee;
                            $day == 'friday' ? reset($weekends) : next($weekends);
                        }
                    }
                }
            }
        }
    }

    public function createScheduleForNotGeneralEmployees($general_employees, $nb_week, &$weekends)
    {
        $objEmployee = new Employee();
        foreach ($general_employees as $teams) {
            foreach ($teams as $team => $employee) {
                $day = current($weekends);
                $check_weekend = $objEmployee->checkWeekend($employee['id'], $nb_week); // есть ли выхоной
                if (empty($check_weekend)) {
                    $check_saturday = $objEmployee->checkWeekendByDayForNotGeneral($nb_week, 'saturday');
                    if (!$check_saturday) { // if current Saturday is available
                        // check latest 2 team weeks
                        $last_saturday = $objEmployee->checkWeekendByTeam($nb_week - 1, $employee['nb_team']);
                        $before_last_saturday = $objEmployee->checkWeekendByTeam($nb_week - 2, $employee['nb_team']);
                        if ((empty($last_saturday) || !$last_saturday->saturday) &&
                            (empty($before_last_saturday) || !$before_last_saturday->saturday)) { // if team hadn't Saturday in latest 2 weeks - weekend is Saturday
                            $objEmployee->insertWeekend($employee['id'], $employee['nb_team'], $employee['id_department'], $nb_week, 'saturday');
                            $data['W' . $nb_week]['saturday'][$employee['id']] = $employee;
                        } else { // if team had Saturday in latest 2 weeks - weekend current weekend day
                            $objEmployee->insertWeekend($employee['id'], $employee['nb_team'], $employee['id_department'], $nb_week, $day);
                            $data['W' . $nb_week][$day][$employee['id']] = $employee;
                            $day == 'friday' ? reset($weekends) : next($weekends);
                        }
                    } else { // if Saturday isn't available
                        $teammate = $objEmployee->checkWeekendByTeam($nb_week, $employee['nb_team']);
                        if (!empty($teammate) && $teammate->saturday == 1 && $teammate->id_employee != $employee['id']) { // if teammate has Saturday weekend - Saturday is weekend for whole team
                            $objEmployee->insertWeekend($employee['id'], $employee['nb_team'], $employee['id_department'], $nb_week, 'saturday');
                            $data['W' . $nb_week]['saturday'][$employee['id']] = $employee;
                        } else {
                            $objEmployee->insertWeekend($employee['id'], $employee['nb_team'], $employee['id_department'], $nb_week, $day);
                            $data['W' . $nb_week][$day][$employee['id']] = $employee;
                            $day == 'friday' ? reset($weekends) : next($weekends);
                        }
                    }
                }
            }
        }
    }

    public function mergeSchedule($general_employees, $other_departments)
    {
        $data = array();
        return $data;
    }

	public function createScheduleFirstTime2()
	{
		$objEmployee = new Employee();
		$general_managers = $objEmployee->getGeneralManagers();
		$general_workers = $objEmployee->getGeneralWorkers();
        $other_employees = $objEmployee->getNotGeneralEmployees();
		$tmp_weekends = $objEmployee->getWeekends();

		// only days
        $weekends = array();
		foreach ($tmp_weekends as $weekend) {
			$weekends[] = strtolower($weekend->day);
		}

		$data = array();
		$weekends1 = $weekends;
		$weekends2 = $weekends;
		$general_employees = $this->sortGeneralEmployees($general_managers, $general_workers);
        $other_employees = $this->sortNotGeneralEmployees($other_employees);
		foreach (range(1, 19, 1) as $nb_week) {
			$general_department = $this->createScheduleForGeneralEmployees($general_employees, $nb_week, $weekends1);
			$other_departments = $this->createScheduleForNotGeneralEmployees($other_employees, $nb_week, $weekends2);
			$data = $this->mergeSchedule($general_department, $other_departments);
		}
		return $data;
	}

	public function continueSchedule($schedules)
    {
        $data = array();
        return $data;
    }
}
