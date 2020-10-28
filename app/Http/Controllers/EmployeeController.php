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
        $this->data['default_image'] = "user.png";
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
            $id_employee = $_POST['id_employee'];
            $objEmployee = new Employee();
            $objSchedule = new Schedule();
            $del_empl = $objEmployee->getEmployeeById($id_employee);
            // delete employee from Employee table
            $objEmployee->deleteEmployee($id_employee);
            // delete employee data from schedule table
            $objSchedule->deleteScheduleByEmployeeId($id_employee);
            // if employee was manager - next employee in team become a manager
            $data = array();
            if ($del_empl['is_manager']) {
                $team_empl = $objEmployee->getEmployeeByTeam($del_empl['nb_team']);
                foreach ($team_empl as $empl) {
                    $objEmployee->setAsManagerById($empl->id);
                    $new_manager = $empl->first_name . ' ' . $empl->last_name;
                    $data['new_manager'] = $new_manager;
                    break;
                }
            }
            return response()->json($data, 200);
        }
        return response()->json(array(), 204);
    }

    public function create()
    {
        $data = $this->data;
        $data['view'] = 'employee.create';
        $data['user_permission'] = '';
        $objEmployee = new Employee();
        $tmp_dept = $objEmployee->getDepartments();
        $departments = array();
        foreach ($tmp_dept as $dept) {
            $departments[$dept->id] = $dept->name;
        }
        $data['departments'] = $departments;
        return view('combined')->with($data);
    }

    public function ajaxCreateEmployee()
    {
        if (!empty($_POST)) {
            $image = '';
            if(isset($_FILES['file']['name'])) {

                /* Getting file name */
                $filename = $_FILES['file']['name'];

                /* Location */
                $new_name = time () . $filename;
                $location = base_path() . "/public/images/" . $new_name;
                $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
                $imageFileType = strtolower($imageFileType);

                /* Valid extensions */
                $valid_extensions = array("jpg", "jpeg", "png");

                $response = 0;
                /* Check file extension */
                if (in_array(strtolower($imageFileType), $valid_extensions)) {
                    /* Upload file */
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $location)) {
                        $image = $new_name;
                    }
                }
            }
            $data = $_POST;
            $data['image'] = $image;
            $objEmployee = new Employee();
            $objEmployee->createEmployee($data);
            return response()->json(array(), 200);
        }
        return response()->json(array(), 204);
    }
}
