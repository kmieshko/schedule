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

	public $data = array();

	public function __construct()
	{
		parent::__construct();

//		$this->not_logged_in();

		$this->data['page_title'] = 'Schedules';
		$this->data['view'] = 'schedules.';
	}

	public function index()
	{
		$data = $this->data;
		$data['view'] .= __METHOD__;
		$data['user_permission'] = '';

		$objSchedule = new Schedule();
		$schedules = $objSchedule->getAllSchedules();
		dd($schedules);

		return view('combined')->with($data);

//	  DB::enableQueryLog();
//		$schedules = $objSchedule->getLastSchedule();
//		if (empty($schedules)) {
//		  $schedule = $this->createScheduleFirstTime();
//			$schedule = $this->createScheduleFirstTime2();
//		} else {
//			$schedule = $this->createSchedule($schedules);
//		}
//		$objSchedule->insertSchedule($schedule);



//	  $query = DB::getQueryLog();
//	  $query = end($query);

//		echo '<pre>' . print_r($schedule, true) . '</pre>';
//	  echo '<pre>' . print_r($query, true) . '</pre>';
//		die;
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
		$objSchedule = new Schedule();
        foreach ($general_employees as $teams) {
            foreach ($teams as $team => $employee) {
                $day = current($weekends);
                $check_weekend = $objSchedule->checkWeekend($employee['id'], $nb_week); // есть ли выхоной
                if (empty($check_weekend)) {
                    $check_saturday = $objSchedule->checkWeekendByDayForGeneral($nb_week, 'saturday');
                    if (!$check_saturday) { // if current Saturday is available
                        // check latest 2 team weeks
                        $last_saturday = $objSchedule->checkWeekendByTeam($nb_week - 1, $employee['nb_team']);
                        $before_last_saturday = $objSchedule->checkWeekendByTeam($nb_week - 2, $employee['nb_team']);
                        if ((empty($last_saturday) || !$last_saturday->saturday) &&
                            (empty($before_last_saturday) || !$before_last_saturday->saturday)) { // if team hadn't Saturday in latest 2 weeks - weekend is Saturday
                            $objSchedule->insertWeekend($employee['id'], $employee['nb_team'], $employee['id_department'], $nb_week, 'saturday');
                            $data['W' . $nb_week]['saturday'][$employee['id']] = $employee;
                        } else { // if team had Saturday in latest 2 weeks - weekend current weekend day
                            $objSchedule->insertWeekend($employee['id'], $employee['nb_team'], $employee['id_department'], $nb_week, $day);
                            $data['W' . $nb_week][$day][$employee['id']] = $employee;
                            $day == 'friday' ? reset($weekends) : next($weekends);
                        }
                    } else { // if Saturday isn't available
                        $teammate = $objSchedule->checkWeekendByTeam($nb_week, $employee['nb_team']);
                        if (!empty($teammate) && $teammate->saturday == 1 && $teammate->id_employee != $employee['id']) { // if teammate has Saturday weekend - Saturday is weekend for whole team
                            $objSchedule->insertWeekend($employee['id'], $employee['nb_team'], $employee['id_department'], $nb_week, 'saturday');
                            $data['W' . $nb_week]['saturday'][$employee['id']] = $employee;
                        } else {
                            $objSchedule->insertWeekend($employee['id'], $employee['nb_team'], $employee['id_department'], $nb_week, $day);
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
		$objSchedule = new Schedule();
        foreach ($general_employees as $teams) {
            foreach ($teams as $team => $employee) {
                $day = current($weekends);
                $check_weekend = $objSchedule->checkWeekend($employee['id'], $nb_week); // есть ли выхоной
                if (empty($check_weekend)) {
                    $check_saturday = $objSchedule->checkWeekendByDayForNotGeneral($nb_week, 'saturday');
                    if (!$check_saturday) { // if current Saturday is available
                        // check latest 2 team weeks
                        $last_saturday = $objSchedule->checkWeekendByTeam($nb_week - 1, $employee['nb_team']);
                        $before_last_saturday = $objSchedule->checkWeekendByTeam($nb_week - 2, $employee['nb_team']);
                        if ((empty($last_saturday) || !$last_saturday->saturday) &&
                            (empty($before_last_saturday) || !$before_last_saturday->saturday)) { // if team hadn't Saturday in latest 2 weeks - weekend is Saturday
							$objSchedule->insertWeekend($employee['id'], $employee['nb_team'], $employee['id_department'], $nb_week, 'saturday');
                            $data['W' . $nb_week]['saturday'][$employee['id']] = $employee;
                        } else { // if team had Saturday in latest 2 weeks - weekend current weekend day
							$objSchedule->insertWeekend($employee['id'], $employee['nb_team'], $employee['id_department'], $nb_week, $day);
                            $data['W' . $nb_week][$day][$employee['id']] = $employee;
                            $day == 'friday' ? reset($weekends) : next($weekends);
                        }
                    } else { // if Saturday isn't available
                        $teammate = $objSchedule->checkWeekendByTeam($nb_week, $employee['nb_team']);
                        if (!empty($teammate) && $teammate->saturday == 1 && $teammate->id_employee != $employee['id']) { // if teammate has Saturday weekend - Saturday is weekend for whole team
							$objSchedule->insertWeekend($employee['id'], $employee['nb_team'], $employee['id_department'], $nb_week, 'saturday');
                            $data['W' . $nb_week]['saturday'][$employee['id']] = $employee;
                        } else {
							$objSchedule->insertWeekend($employee['id'], $employee['nb_team'], $employee['id_department'], $nb_week, $day);
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

	public function getStartAndEndDate($week, $year) {
		$dto = new DateTime();
		$dto->setISODate($year, $week);
		$result['week_start'] = $dto->format('Y-m-d');
		$dto->modify('+6 days');
		$result['week_end'] = $dto->format('Y-m-d');
		return $result;
	}

	public function getWeekNumber($date)
	{
		$week_nb = idate('W', strtotime($date));
		return $week_nb;
	}

	public function createScheduleFirstTime($date = null, $week_amount = 9)
	{
		$objEmployee = new Employee();
		$objSchedule = new Schedule();
		$general_managers = $objEmployee->getGeneralManagers();
		$general_workers = $objEmployee->getGeneralWorkers();
        $other_employees = $objEmployee->getNotGeneralEmployees();
		$tmp_weekends = $objSchedule->getWeekends();

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
