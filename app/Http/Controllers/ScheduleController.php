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
		$schedules = $objSchedule->getAllSchedules();
		$data['schedules'] = $this->sortSchedules($schedules);

		// nb_week => [week_start, week_end]
		$tmp_weeks = array_keys($data['schedules']);
		$weeks = array();
		foreach ($tmp_weeks as $week) {
			$weeks[$week] = $this->getStartAndEndDate($week, date('Y'));
		}

		$weekends = $this->getOnlyDays($objSchedule->getWeekends());
		$weekends[] = 'sunday';

		$data['weeks'] = $weeks;
		$data['weekends'] = $weekends;
		$data['current_week'] = $this->getWeekNumber(date('m/d/Y'));
		$data['current_week_dates'] =  $this->getStartAndEndDate($data['current_week'], date('Y'));
		return view('combined')->with($data);
	}

	public function create()
    {
        $data = $this->data;
        $data['view'] = 'schedules.create';
        $data['user_permission'] = '';
        $objEmployee = new Employee();
        $employees = $objEmployee->getAllEmployeesWithDepartments();
        $data['employees'] = $employees;
        $data['latest_week'] = Schedule::max('id_week');
        $dates = Schedule::select('week_start', 'week_end')->where('id_week', '=', $data['latest_week'])->limit(1)->get();
        $week_start = isset($dates[0]) ? date('m/d/Y', strtotime($dates[0]['week_start'])) : '';
        $week_end = isset($dates[0]) ? date('m/d/Y', strtotime($dates[0]['week_end'])) : '';
        $data['week_start'] = $week_start;
        $data['week_end'] = $week_end;
        return view('combined')->with($data);
    }

	public function sortSchedules($schedules)
	{
		$data = array();
		$objSchedule = new Schedule();
		$objEmployee = new Employee();
		$tmp_employees = $objEmployee->getAllEmployees();
		$weekends = $this->getOnlyDays($objSchedule->getWeekends());

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
                foreach ($weekends as $weekend) {
                    $data[$schedule->id_week][$schedule->id_employee][$weekend] = $schedule->$weekend;
                }
                $data[$schedule->id_week][$schedule->id_employee]['sunday'] = 1;
            }
		}
		return $data;
	}

    public function sortGeneralEmployees($managers, $workers, $excluded_employees)
    {
        $result = array();
        foreach ($managers as $manager) {
        	if (array_search($manager->id, $excluded_employees) === FALSE) {
        		$result['T' . $manager->nb_team][$manager->id] = (array)$manager;
			}
			foreach ($workers as $worker) {
				if (array_search($worker->id, $excluded_employees) === FALSE) {
					if ($manager->nb_team == $worker->nb_team) {
						$result['T' . $worker->nb_team][$worker->id] = (array)$worker;
					}
				}
			}
        }
        return $result;
    }

    public function sortNonGeneralEmployees($employees, $excluded_employees)
    {
        $result = array();
        foreach ($employees as $employee) {
			if (array_search($employee->id, $excluded_employees) === FALSE) {
				$result['T' . $employee->nb_team][$employee->id] = (array)$employee;
			}
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
                            (empty($before_last_saturday) || !$before_last_saturday->saturday)) { // if team hadn't Saturday in latest 2 weeks - weekend is current Saturday
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

	public function getOnlyDays($weekends)
	{
		$result = array();
		foreach ($weekends as $weekend) {
			$result[] = strtolower($weekend->day);
		}
		return $result;
	}

	public function shiftWeekends(&$weekends, $latest_day)
    {
        while (1) {
            if (current($weekends) == $latest_day) {
                if (current($weekends) == 'friday') reset($weekends);
                else next($weekends);
                break;
            }
            if (current($weekends) == 'friday') reset($weekends);
            else next($weekends);
        }
    }

	public function createSchedule($date, $weeks_amount, $excluded_employees)
	{
		$objEmployee = new Employee();
		$objSchedule = new Schedule();
		$general_managers = $objEmployee->getGeneralManagers();
		$general_workers = $objEmployee->getGeneralWorkers();
		$other_employees = $objEmployee->getNonGeneralEmployees();
		$weekends = $this->getOnlyDays($objSchedule->getWeekends());

		$weekends1 = $weekends;
		$weekends2 = $weekends;
		$latest_week = Schedule::max('id_week');
		if (!empty($latest_week)) {
			$latest_day_for_general = array_search ('1', (array)$objSchedule->getLatestDayForGeneral($latest_week));
			$latest_day_for_non_general = array_search ('1', (array)$objSchedule->getLatestDayForNonGeneral($latest_week));
			$this->shiftWeekends($weekends1, $latest_day_for_general);
			$this->shiftWeekends($weekends2, $latest_day_for_non_general);
		}

		$general_employees = $this->sortGeneralEmployees($general_managers, $general_workers, $excluded_employees);
        $other_employees = $this->sortNonGeneralEmployees($other_employees, $excluded_employees);
		$week_in_year = $this->getWeekNumber($date['start']);
		foreach (range(1, $weeks_amount, 1) as $week) {
			$nb_week = $week + $week_in_year;
			$week_dates = $this->getStartAndEndDate($nb_week, $date['year']);
			$this->createScheduleForGeneralEmployees($general_employees, $nb_week, $week_dates, $weekends1);
			$this->createScheduleForNonGeneralEmployees($other_employees, $nb_week, $week_dates, $weekends2);
		}
	}

	public function ajaxCreateSchedule()
    {
        if (!empty($_POST)) {
			$weeks_amount = 1;
        	if (isset($_POST['weeks_amount'])) {
				$weeks_amount = intval($_POST['weeks_amount']);
				$weeks_amount = $weeks_amount < 1 ? 1 : $weeks_amount;
			}
            $date = array();
            $latest_date = Schedule::max('week_end');
            if (!empty($latest_date)) {
                $date['start'] = date('Y-m-d', strtotime($latest_date));
                $date['end'] = date('Y-m-d', strtotime($latest_date));
                $date['year'] = date('Y', strtotime($latest_date));
            } else {
				$date['start'] = date('Y-m-d', strtotime('Monday'));
				$date['end'] = date('Y-m-d', strtotime('Sunday'));
				$date['year'] = date('Y');
			}
            $excluded_employees = array();
            if (isset($_POST['employees'])) {
                $excluded_employees = $_POST['employees'];
            }
            $this->createSchedule($date, $weeks_amount, $excluded_employees);
            $data['week_end'] = date('m/d/Y', strtotime(Schedule::max('week_end')));
            $data['week_start'] = date('m/d/Y', strtotime(Schedule::max('week_start')));
            $data['latest_week'] = Schedule::max('id_week');
            return response()->json(array('data'=> $data), 200);
        } else header("HTTP/1.0 403 Forbidden");
    }
}
