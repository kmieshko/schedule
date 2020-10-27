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

class EmployeeController extends Controller
{
    public $data = array();
    public $request = null;

    public function __construct()
    {
        parent::__construct();

//		$this->not_logged_in();

        $this->request = new Request();
        $this->data['page_title'] = 'Employee';
    }

    public function index()
    {
        $data = $this->data;
        $data['view'] = 'employee.index';
        $data['user_permission'] = '';
        $objEmployee = new Employee();
        $employees = $objEmployee->getAllEmployeesWithDepartments();
        $data['employees'] = $employees;
        return view('combined')->with($data);
    }

    public function ajaxDeleteEmployee()
    {
        if (!empty($_POST) && isset($_POST['id_employee'])) {
            $objEmployee = new Employee();
            // if employee was manager - next employee in team become a manager
            //$employee = $objEmployee->deleteEmployee($_POST['id_employee']);
            //dd($employee);
            return response()->json(array(), 200);
        }
        return response()->json(array(), 204);
    }
}
