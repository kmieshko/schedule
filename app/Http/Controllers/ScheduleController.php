<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Schedule;
use App\Employee;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Debug\Debug;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DateTime;

class ScheduleController extends Controller
{

	public $data = array();
	public $request = null;

	public function __construct()
	{
		parent::__construct();

//		$this->not_logged_in();

		$this->request = new Request();
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

	public function index2()
	{
		$data = $this->data;
		$data['view'] = 'schedules.index2';
		$data['user_permission'] = '';

		$objSchedule = new Schedule();
		$tmp_schedules = $objSchedule->getAllWeeksSchedules();
		$schedules = array();
		foreach ($tmp_schedules as $item) {
			$schedules[$item->id_week] = $item->id_week;
		}
		$data['schedules'] = $schedules;

		// nb_week => [week_start, week_end]
		$tmp_weeks = array_keys($data['schedules']);
		$weeks = array();
		foreach ($tmp_weeks as $week) {
			$weeks[$week] = $this->getStartAndEndDate($week, date('Y'));
		}
		$data['weeks'] = $weeks;
		$data['current_week'] = $this->getWeekNumber(date('m/d/Y'));
		$data['current_week_dates'] =  $this->getStartAndEndDate($data['current_week'], date('Y'));
		return view('combined')->with($data);
	}

	public function ajaxScheduleByWeek()
	{
		$id_week = $_POST['id_week'];
		$objSchedule = new Schedule();
		$schedules = $objSchedule->getScheduleByWeek($id_week);
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
		$data['week_start'] = date('m/d/Y', strtotime($weeks[$id_week]['week_start']));
		$data['week_end'] = date('m/d/Y', strtotime($weeks[$id_week]['week_end']));
		return response()->json($data, 200);
	}

	public function create()
    {
        $data = $this->data;
        $data['view'] = 'schedules.create';
        $data['user_permission'] = '';
        $objEmployee = new Employee();
        $employees = $objEmployee->getAllEmployeesWithDepartments();
        $data['employees'] = $employees;
        $latest_week = Schedule::select('id_week')->orderBy('id', 'desc')->limit(1)->get();
        $data['latest_week'] = isset($latest_week[0]) ? $latest_week[0]['id_week'] : '';
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
        $i = 1;
        foreach ($managers as $manager) {
        	if (array_search($manager->id, $excluded_employees) === FALSE) {
        		$result[$i][$manager->id] = (array)$manager;
			}
			foreach ($workers as $worker) {
				if (array_search($worker->id, $excluded_employees) === FALSE) {
					if ($manager->nb_team == $worker->nb_team) {
						$result[$i][$worker->id] = (array)$worker;
					}
				}
			}
			$i++;
        }
        return $result;
    }

    public function sortNonGeneralEmployees($employees, $excluded_employees)
    {
        $result = array();
		$i = 1;
        foreach ($employees as $employee) {
			if (array_search($employee->id, $excluded_employees) === FALSE) {
				$result[$i][$employee->id] = (array)$employee;
			}
			$i++;
        }
        return $result;
    }

    public function createScheduleAlgorithm($employees, $nb_week, $week_dates, $is_general, &$weekends)
    {
        $objSchedule = new Schedule();
		$period = 3; // period when Saturday = weekend must retry
		$tmp_key = $period;
        foreach ($employees as $key => $teams) {
            foreach ($teams as $employee) {
                $day = current($weekends);
                $check_weekend = $objSchedule->checkWeekend($employee['id'], $nb_week); // есть ли выхоной
                if (empty($check_weekend)) {
                    if ($is_general) {
                        $is_saturday_busy = $objSchedule->checkWeekendByDayForGeneral($nb_week, 'saturday');
                    } else {
						$is_saturday_busy = $objSchedule->checkWeekendByDayForNonGeneral($nb_week, 'saturday');
                    }
                    if (!$is_saturday_busy || $key % $period == $tmp_key) { // if current Saturday is available
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
                            $tmp_key = $key;
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

        // sorting by a team with condition that manager has to be a first worker in a team's array
        // and removing excluded workers
        $general_employees = $this->sortGeneralEmployees($general_managers, $general_workers, $excluded_employees);

        // sorting by a team without any conditions, because non general employees haven't managers in team
        // and removing excluded workers
        $other_employees = $this->sortNonGeneralEmployees($other_employees, $excluded_employees);

		// if it's not a first scheduling - check last day and shifting weekends
		$latest_week = Schedule::select('id_week')->orderBy('id', 'desc')->limit(1)->get();
		if (isset($latest_week[0])) {
            $latest_week = $latest_week[0]['id_week'];
			$latest_day_for_general = array_search ('1', (array)$objSchedule->getLatestDayForGeneral($latest_week));
			$latest_day_for_non_general = array_search ('1', (array)$objSchedule->getLatestDayForNonGeneral($latest_week));
			$this->shiftWeekends($weekends1, $latest_day_for_general);
			$this->shiftWeekends($weekends2, $latest_day_for_non_general);
		}

		// main loop with algorithm
		for($i = 0; $i < $weeks_amount; $i++) {
            $week_in_year = $this->getWeekNumber($date['start']);
			$nb_week = $this->getWeekNumber($date['start'] . "+" . 7 * $i ." day");
			$week_dates = $this->getStartAndEndDate($nb_week, $date['year']);
			// create for general employees
			$is_general = true;
            $this->createScheduleAlgorithm($general_employees, $nb_week, $week_dates, $is_general, $weekends1);
            // create for non-general employees
            $is_general = false;
            $this->createScheduleAlgorithm($other_employees, $nb_week, $week_dates, $is_general, $weekends1);
		}
	}

	public function ajaxCreateSchedule()
    {
        $weeks_amount = 1;
        if (isset($_POST['weeks_amount'])) {
            $weeks_amount = intval($_POST['weeks_amount']);
            $weeks_amount = $weeks_amount < 1 ? 1 : $weeks_amount;
        }
        $date = array();
        $latest_date = Schedule::select('week_end')->orderBy('id', 'desc')->limit(1)->get();
        if (isset($latest_date[0])) {
            $latest_date = $latest_date[0]['week_end'];
            $date['start'] = date('Y-m-d', strtotime($latest_date . "+7 day"));
            $date['end'] = date('Y-m-d', strtotime($latest_date . "+7 day"));
            $date['year'] = date('Y', strtotime($latest_date . "+7 day"));
        } else {
            $next_week = strtotime('next week');
            $date['start'] = date("Y-m-d", strtotime('monday', $next_week));
            $date['end'] = date("Y-m-d", strtotime('sunday', $next_week));
            $date['year'] = date('Y');
        }
        $excluded_employees = array();
        if (isset($_POST['employees'])) {
            $excluded_employees = $_POST['employees'];
        }
        $this->createSchedule($date, $weeks_amount, $excluded_employees);
        $data['week_end'] = date('m/d/Y', strtotime(Schedule::select('week_end')->orderBy('id', 'desc')->limit(1)->get()[0]['week_end']));
        $data['week_start'] = date('m/d/Y', strtotime(Schedule::select('week_start')->orderBy('id', 'desc')->limit(1)->get()[0]['week_start']));
        $latest_week = Schedule::select('id_week')->orderBy('id', 'desc')->limit(1)->get();
        $data['latest_week'] = isset($latest_week[0]) ? $latest_week[0]['id_week'] : '';
        return response()->json(array('data'=> $data), 200);
    }

    public function ajaxDownloadSchedule(Request $request)
	{
	    if ($request->session()->has('nb_week')) {
            $nb_week = $request->session()->pull('nb_week');
        } else {
            $nb_week = $_GET['id_week'];
            $request->session()->put('nb_week', $nb_week);
        }
            $objSchedule = new Schedule();
            $result = $objSchedule->getScheduleByWeek($nb_week);
            $teams = array();
            $week_start = '';
            $week_end = '';
            foreach ($result as $value) {
                $teams[$value->nb_team][] = (array)$value;
                if (empty($week_start)) $week_start = $value->week_start;
                if (empty($week_end)) $week_end = $value->week_end;
            }
            $data['nb_week'] = $nb_week;
            $schedules = $objSchedule->getScheduleByWeek($nb_week);
            $data['schedules'] = $this->sortSchedules($schedules);
            $data['schedules'] = $schedules;

            // nb_week => [week_start, week_end]
            $tmp_weeks = $data['schedules'];
            $weeks = array();
            foreach ($tmp_weeks as $week) {
                $weeks[$week->id_week] = $this->getStartAndEndDate($week->id_week, date('Y'));
            }
            $data['weeks'] = $weeks;
            $data['current_week'] = $this->getWeekNumber(date('m/d/Y'));
            $data['current_week_dates'] =  $this->getStartAndEndDate($data['current_week'], date('Y'));
            $weekends = $this->getOnlyDays($objSchedule->getWeekends());
            $weekends[] = 'sunday';
            $data['weekends'] = $weekends;
            $this->downloadScheduleExcel($data);
	}

    function getNameFromNumber($num) {
        $numeric = ($num - 1) % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval(($num - 1) / 26);
        if ($num2 > 0) {
            return getNameFromNumber($num2) . $letter;
        } else {
            return $letter;
        }
    }

	public function downloadScheduleExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $title = 'WEEK #' . $data['nb_week'] . ' ' . date('m/d/Y', strtotime($data['weeks'][$data['nb_week']]['week_start'])) . ' - ' . date('m/d/Y', strtotime($data['weeks'][$data['nb_week']]['week_end']));
        $sheet->getStyle('A:H')->getFont()->setName('Calibri')->setSize('14');
        $sheet->setCellValue('B1', $title);
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN //fine border
                ]
            ]
        ];

        $sheet->getStyle('A1:H' . (3 + count($data['schedules'])))->applyFromArray($styleArray);
        $sheet->mergeCells('B1:H1');
        $sheet->mergeCells('A1:A3');
        $sheet->getStyle('B:H')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('B1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('B1')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $styleArray = array(
            'borders' => array(
                'outline' => array(
                    'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                ),
            ),
        );
        $week_dates = array();
        foreach ($data['weekends'] as $key => $weekend) {
            $week_dates[] = date('m/d/Y', strtotime($data['weeks'][$data['nb_week']]['week_start'] . '+'.$key.' days'));
        }
        $sheet->fromArray($data['weekends'], NULL, 'B2');
        $sheet->fromArray($week_dates, NULL, 'B3');
        $row = 4;
        foreach ($data['schedules'] as $nb_week => $employee) {
            $col = 1;
            $sheet->setCellValueByColumnAndRow($col, $row, $employee->last_name . ' ' . $employee->first_name);
            $col++;
            foreach ($data['weekends'] as $key => $weekend) {
                $letter = $this->getNameFromNumber($col + $key);
                $sheet->getColumnDimension($letter)->setAutoSize(true);
                if($weekend == 'sunday' || !$employee->$weekend) {
                    // set number
                    //$sheet->setCellValueByColumnAndRow($col + $key, $row, 0);

                    // set color
                    $sheet->getStyle($letter. $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFC000');
                } else {
                    // set number
                    // $sheet->setCellValueByColumnAndRow($col + $key, $row, $employee->$weekend);

                    $sheet->setCellValueByColumnAndRow($col + $key, $row, 'OFF');
                    // set color
                    $sheet->getStyle($letter. $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
                }
            }
            $row++;
        }
        $tmp_file = tempnam(sys_get_temp_dir(), 'Excel');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmp_file);
        $document = file_get_contents($tmp_file);
        unlink($tmp_file);
        header("Content-Disposition: attachment; filename= $title.xlsx");
        header('Content-Type: application/excel');
        echo $document;
    }
}
