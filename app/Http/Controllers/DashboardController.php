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
use Asana;

class DashboardController extends Controller
{

    public $data = array();
    public $request = null;
    private $client;
    private $allJobs = 1190695568232322;
    private $accessToken = '1/1176229093318817:feda44c6f1d61980aeb42b015350293c';

    public function __construct()
    {
        parent::__construct();

//		$this->not_logged_in();

        $this->request = new Request();
        $this->data['page_title'] = 'Dashboard';
        $this->client = Asana\Client::accessToken($this->accessToken);
    }

    public function index()
    {
        $data = $this->data;
        $data['view'] = 'dashboard.index';
        $data['user_permission'] = '';
        return view('combined')->with($data);
    }

    public function ajaxGetDashboardInfo()
    {
        $tasks = $this->client->tasks->getTasksForProject($this->allJobs, array('opt_fields' => 'custom_fields,completed'), array('opt_pretty' => 'true'));
        $data['tasks'] = array();
        $data['tasks_info'] = array();
        foreach ($tasks as $task) {
            foreach ($task as $custom_field) {
                if (is_array($custom_field)) {
                    foreach ($custom_field as $array) {
                        if ($array->name == 'Job Status') {
                            $data['tasks'][$task->gid]['id'] = $task->gid;
                            $data['tasks'][$task->gid]['completed'] = $task->completed;
                            $data['tasks'][$task->gid]['job_status'] = $array->enum_value->name;
                            $data['tasks_info']["{$array->enum_value->name}"] = 0;
                        }
                    }
                }
            }
        }
        $data['tasks_info']['INVOICED'] = 0;
        foreach ($data['tasks'] as $task) {
            if ($task['completed']) {
                $data['tasks_info']['INVOICED'] += 1;
            } else {
                $data['tasks_info']["{$task['job_status']}"] += 1;
            }
        }
        return response()->json($data, 200);
    }
}
