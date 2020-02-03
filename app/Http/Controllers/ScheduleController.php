<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Schedule;
use App\Employee;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Debug\Debug;
use DateTime;

class ScheduleController extends Controller
{

	public $data = array();

	public function __construct()
	{
		parent::__construct();

//		$this->not_logged_in();

		$this->data['page_title'] = 'Schedules';
	}

	public function index()
	{
		$data = $this->data;
		$data['view'] = 'schedules.index';
		$data['user_permission'] = '';

		$objSchedule = new Schedule();
//		$this->createScheduleFirstTime();
//		die;
		$schedules = $objSchedule->getAllSchedules();
		$data['schedules'] = $this->sortSchedules($schedules);

		// nb_week => [week_start, week_end]
		$tmp_weeks = array_keys($data['schedules']);
		$weeks = array();
		foreach ($tmp_weeks as $week) {
			$weeks[$week] = $this->getStartAndEndDate($week, date('Y'));
		}

		// only days
		$tmp_weekends = $objSchedule->getWeekends();
		$weekends = array();
		foreach ($tmp_weekends as $weekend) {
			$weekends[] = strtolower($weekend->day);
		}
		$weekends[] = 'sunday';

		$data['weeks'] = $weeks;
		$data['weekends'] = $weekends;
		$data['current_week'] = $this->getWeekNumber(date('m/d/Y'));
		$data['current_week_dates'] =  $this->getStartAndEndDate($data['current_week'], date('Y'));
		return view('combined')->with($data);
	}

	public function sortSchedules($schedules)
	{
		$data = array();
		$objSchedule = new Schedule();
		$objEmployee = new Employee();
		$tmp_employees = $objEmployee->getAllEmployees();
		$tmp_week_days = $objSchedule->getWeekends();

		// only days
		$week_days = array();
		foreach ($tmp_week_days as $week_day) {
			$week_days[] = strtolower($week_day->day);
		}

		// id_employee => employee info
		$employees = array();
		foreach ($tmp_employees as $employee) {
			$employees[$employee->id] = (array)$employee;
		}

		// [#WEEK] => [id_employee => (employee info + weekend info)]
		foreach ($schedules as $schedule) {
		    if (isset($employees[$schedule->id_employee])) {
                $data[$schedule->id_week][$schedule->id_employee]['id_employee'] = $schedule->id_employee;
                $data[$schedule->id_week][$schedule->id_employee]['first_name'] = $employees[$schedule->id_employee]['first_name'];
                $data[$schedule->id_week][$schedule->id_employee]['last_name'] = $employees[$schedule->id_employee]['last_name'];
                foreach ($week_days as $week_day) {
                    $data[$schedule->id_week][$schedule->id_employee][$week_day] = $schedule->$week_day;
                }
                $data[$schedule->id_week][$schedule->id_employee]['sunday'] = 1;
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

    public function sortNonGeneralEmployees($employees)
    {
        $result = array();
        foreach ($employees as $employee) {
            $result['T' . $employee->nb_team][$employee->id] = (array)$employee;
        }
        return $result;
    }

    public function createScheduleForGeneralEmployees($general_employees, $nb_week, $week_dates, &$weekends)
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
							$insert_data = array(
								'id_week' => $nb_week,
								'id_employee' => $employee['id'],
								'nb_team' => $employee['nb_team'],
								'id_department' => $employee['id_department'],
								'day' => 'saturday',
								'week_start' => $week_dates['week_start'],
								'week_end' => $week_dates['week_end'],
							);
							$objSchedule->insertWeekend($insert_data);
                            $data['W' . $nb_week]['saturday'][$employee['id']] = $employee;
                        } else { // if team had Saturday in latest 2 weeks - weekend current weekend day
							$insert_data = array(
								'id_week' => $nb_week,
								'id_employee' => $employee['id'],
								'nb_team' => $employee['nb_team'],
								'id_department' => $employee['id_department'],
								'day' => $day,
								'week_start' => $week_dates['week_start'],
								'week_end' => $week_dates['week_end'],
							);
							$objSchedule->insertWeekend($insert_data);
                            $data['W' . $nb_week][$day][$employee['id']] = $employee;
                            $day == 'friday' ? reset($weekends) : next($weekends);
                        }
                    } else { // if Saturday isn't available
                        $teammate = $objSchedule->checkWeekendByTeam($nb_week, $employee['nb_team']);
                        if (!empty($teammate) && $teammate->saturday == 1 && $teammate->id_employee != $employee['id']) { // if teammate has Saturday weekend - Saturday is weekend for whole team
							$insert_data = array(
								'id_week' => $nb_week,
								'id_employee' => $employee['id'],
								'nb_team' => $employee['nb_team'],
								'id_department' => $employee['id_department'],
								'day' => 'saturday',
								'week_start' => $week_dates['week_start'],
								'week_end' => $week_dates['week_end'],
							);
							$objSchedule->insertWeekend($insert_data);
                            $data['W' . $nb_week]['saturday'][$employee['id']] = $employee;
                        } else {
							$insert_data = array(
								'id_week' => $nb_week,
								'id_employee' => $employee['id'],
								'nb_team' => $employee['nb_team'],
								'id_department' => $employee['id_department'],
								'day' => $day,
								'week_start' => $week_dates['week_start'],
								'week_end' => $week_dates['week_end'],
							);
                        	$objSchedule->insertWeekend($insert_data);
                            $data['W' . $nb_week][$day][$employee['id']] = $employee;
                            $day == 'friday' ? reset($weekends) : next($weekends);
                        }
                    }
                }
            }
        }
    }

    public function createScheduleForNonGeneralEmployees($general_employees, $nb_week, $week_dates, &$weekends)
    {
		$objSchedule = new Schedule();
        foreach ($general_employees as $teams) {
            foreach ($teams as $team => $employee) {
                $day = current($weekends);
                $check_weekend = $objSchedule->checkWeekend($employee['id'], $nb_week); // есть ли выхоной
                if (empty($check_weekend)) {
                    $check_saturday = $objSchedule->checkWeekendByDayForNonGeneral($nb_week, 'saturday');
                    if (!$check_saturday) { // if current Saturday is available
                        // check latest 2 team weeks
                        $last_saturday = $objSchedule->checkWeekendByTeam($nb_week - 1, $employee['nb_team']);
                        $before_last_saturday = $objSchedule->checkWeekendByTeam($nb_week - 2, $employee['nb_team']);
                        if ((empty($last_saturday) || !$last_saturday->saturday) &&
                            (empty($before_last_saturday) || !$before_last_saturday->saturday)) { // if team hadn't Saturday in latest 2 weeks - weekend is Saturday
							$insert_data = array(
								'id_week' => $nb_week,
								'id_employee' => $employee['id'],
								'nb_team' => $employee['nb_team'],
								'id_department' => $employee['id_department'],
								'day' => 'saturday',
								'week_start' => $week_dates['week_start'],
								'week_end' => $week_dates['week_end'],
							);
							$objSchedule->insertWeekend($insert_data);
                            $data['W' . $nb_week]['saturday'][$employee['id']] = $employee;
                        } else { // if team had Saturday in latest 2 weeks - weekend current weekend day
							$insert_data = array(
								'id_week' => $nb_week,
								'id_employee' => $employee['id'],
								'nb_team' => $employee['nb_team'],
								'id_department' => $employee['id_department'],
								'day' => $day,
								'week_start' => $week_dates['week_start'],
								'week_end' => $week_dates['week_end'],
							);
							$objSchedule->insertWeekend($insert_data);
							$data['W' . $nb_week][$day][$employee['id']] = $employee;
                            $day == 'friday' ? reset($weekends) : next($weekends);
                        }
                    } else { // if Saturday isn't available
                        $teammate = $objSchedule->checkWeekendByTeam($nb_week, $employee['nb_team']);
                        if (!empty($teammate) && $teammate->saturday == 1 && $teammate->id_employee != $employee['id']) { // if teammate has Saturday weekend - Saturday is weekend for whole team
							$insert_data = array(
								'id_week' => $nb_week,
								'id_employee' => $employee['id'],
								'nb_team' => $employee['nb_team'],
								'id_department' => $employee['id_department'],
								'day' => 'saturday',
								'week_start' => $week_dates['week_start'],
								'week_end' => $week_dates['week_end'],
							);
							$objSchedule->insertWeekend($insert_data);
                            $data['W' . $nb_week]['saturday'][$employee['id']] = $employee;
                        } else {
							$insert_data = array(
								'id_week' => $nb_week,
								'id_employee' => $employee['id'],
								'nb_team' => $employee['nb_team'],
								'id_department' => $employee['id_department'],
								'day' => $day,
								'week_start' => $week_dates['week_start'],
								'week_end' => $week_dates['week_end'],
							);
                        	$objSchedule->insertWeekend($insert_data);
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

	public function createScheduleFirstTime($date = array(), $week_amount = 9)
	{
		if (empty($date)) {
			$date['start'] = date('Y-m-d', strtotime('Monday'));
			$date['end'] = date('Y-m-d', strtotime('Sunday'));
			$date['year'] = date('Y');
		}
		$objEmployee = new Employee();
		$objSchedule = new Schedule();
		$general_managers = $objEmployee->getGeneralManagers();
		$general_workers = $objEmployee->getGeneralWorkers();
		$other_employees = $objEmployee->getNonGeneralEmployees();
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
        $other_employees = $this->sortNonGeneralEmployees($other_employees);
		$week_in_year = $this->getWeekNumber($date['start']);
		foreach (range(1, $week_amount, 1) as $week) {
			$nb_week = $week + $week_in_year;
			$week_dates = $this->getStartAndEndDate($nb_week, $date['year']);
			$general_department = $this->createScheduleForGeneralEmployees($general_employees, $nb_week, $week_dates, $weekends1);
			$other_departments = $this->createScheduleForNonGeneralEmployees($other_employees, $nb_week, $week_dates, $weekends2);
			$data = $this->mergeSchedule($general_department, $other_departments);
		}
		return $data;
	}
}
