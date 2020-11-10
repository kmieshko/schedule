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

    public function __construct()
    {
        parent::__construct();

//		$this->not_logged_in();

        $this->request = new Request();
        $this->data['page_title'] = 'Dashboard';
    }

    public function index()
    {
        $data = $this->data;
        $data['view'] = 'dashboard.index';
        $data['user_permission'] = '';

        $client = Asana\Client::accessToken('1/1176229093318817:feda44c6f1d61980aeb42b015350293c');
        $projectId = 1190695568232322;
        $tasks = $client->tasks->getTasksForProject($projectId, array('opt_fields' => 'custom_fields.enum_value.Job Status'), array('opt_pretty' => 'true'));
        foreach ($tasks as $task) {
            $data['tasks'][] = $task;
        }
        dd($data['tasks']);
        return view('combined')->with($data);
    }
}
