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
        $this->data['grr_template_front'] = "grr_template_front.png";
        $this->data['grr_template_back'] = "grr_template_back.png";
        $this->data['baikal_template_front'] = "baikal_template_front.png";
        $this->data['baikal_template_back'] = "baikal_template_back.png";
    }

    public function index()
    {
        $data = $this->data;
        $data['view'] = 'employee.index';
        $data['user_permission'] = '';
        $objEmployee = new Employee();
        $employees = $objEmployee->getAllEmployeesWithDepartments();
        $data['employees'] = $employees;
        $tmp_dept = $objEmployee->getDepartments();
        $departments = array();
        foreach ($tmp_dept as $dept) {
            $departments[$dept->id] = $dept->name;
        }
        $data['departments'] = $departments;
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
                $location = base_path() . "/public/images/employee_photo/" . $new_name;
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

    public function ajaxGetGrrCardTemplate()
    {
        $img_front = file_get_contents(base_path() . "/public/images/" . $this->data['grr_template_front']);
        $img_back = file_get_contents(base_path() . "/public/images/" . $this->data['grr_template_back']);
        $data['front_template_base64'] = base64_encode($img_front);
        $data['back_template_base64'] = base64_encode($img_back);
        return response()->json($data, 200);
    }

    public function imageDecode($image, $extension) {
        $mime = '';
        switch ($extension) {
            case 'jpg':
                $mime = 'image/jpeg';
                break;
            case 'png':
                $mime = 'image/png';
                break;
            case 'gif':
                $mime = 'image/gif';
                break;

        }
        $result = str_replace('data:'. $mime .';base64,', '', $image);
        $result = str_replace(' ', '+', $result);
        $result = base64_decode($result);
        return $result;
    }

    public function getExtension($img) {

        $extension = str_replace( 'data:', '', stristr($img, ';base64,', true));
        switch ($extension) {
            case 'image/jpeg':
                $extension = 'jpg';
                break;
            case 'image/png':
                $extension = 'png';
                break;
            case 'image/gif':
                $extension = 'gif';
                break;
        }
        return $extension;
    }

    public function ajaxSaveIdCard()
    {
        if (!empty($_POST)) {
            $image_front = $_POST["image_front"];
            $image_back = $_POST["image_back"];
            $id_card = $_POST["id_card"];
            $id_employee = $_POST["id_employee"];
            $extension = $this->getExtension($image_front);
            $decoded_front = $this->imageDecode($image_front, $extension);
            $decoded_back = $this->imageDecode($image_back, $extension);
            $name_front = $id_card . '_front.' . $extension;
            $name_back = $id_card . '_back.' . $extension;
            if (!file_exists(base_path() . '/public/images/id_card')) mkdir(base_path() . '/public/images/id_card');
            $path_front = base_path() . '/public/images/id_card/' . $name_front;
            $path_back = base_path() . '/public/images/id_card/' . $name_back;
            if ($decoded_front === false || $decoded_back === false) {
                return response()->json(array(), 400);
            } else {
                file_put_contents($path_front, $decoded_front);
                file_put_contents($path_back, $decoded_back);
                $objEmployee = new Employee();
                $data['id_card'] = $id_card;
                $data['id'] = $id_employee;
                $objEmployee->saveIdCard($data);
                return response()->json(array(), 200);
            }
        }
        return response()->json(array(), 400);
    }

    public function ajaxGetIdCard()
    {
        if (!empty($_POST)) {
            $objEmployee = new Employee();
            $img = $objEmployee->getIdCard($_POST);
            $img_name = $img->id_card;
            if (empty($img_name)) return response()->json(array(), 204);
            $data['front_id_card'] = '/public/images/id_card/' . $img_name . '_front.png';
            $data['back_id_card'] = '/public/images/id_card/' . $img_name . '_back.png';
            $data['id_card'] = $img_name;
            return response()->json($data, 200);
        }
        return response()->json(array(), 404);
    }

    public function ajaxSaveChanges()
    {
        if (!empty($_POST)) {
            $image = '';
            if(isset($_FILES['file']['name'])) {

                /* Getting file name */
                $filename = $_FILES['file']['name'];

                /* Location */
                $new_name = time () . $filename;
                if (!file_exists(base_path() . '/public/images/employee_photo')) mkdir(base_path() . '/public/images/employee_photo');
                $location = base_path() . "/public/images/employee_photo/" . $new_name;
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

            // delete previous id_card
            $objEmployee = new Employee();
            $data['id'] = $_POST['id_employee'];
            $img = $objEmployee->getIdCard($data);
            $img_name = $img->id_card;
            if (!empty($img_name)) {
                unlink(base_path() . '/public/images/id_card/' . $img_name . '_front.png');
                unlink(base_path() . '/public/images/id_card/' . $img_name . '_back.png');
            }
            $data['id_card'] = NULL;
            $objEmployee->saveIdCard($data);

            // save changes
            if (!empty($image)) {
                $data['image'] =  $image;
            }
            $data['first_name'] = $_POST['first_name'];
            $data['last_name'] = $_POST['last_name'];
            $data['position'] = $_POST['position'];
            $objEmployee->saveChanges($data);
            return response()->json($data, 200);
        }
        return response()->json(array(), 404);
    }

    public function customIdCard()
    {
        $data = $this->data;
        $data['view'] = 'employee.custom_id_card';
        $data['user_permission'] = '';
        return view('combined')->with($data);
    }

    public function ajaxGetCardTemplates()
    {
        $grr_img_front = file_get_contents(base_path() . "/public/images/" . $this->data['grr_template_front']);
        $grr_img_back = file_get_contents(base_path() . "/public/images/" . $this->data['grr_template_back']);
        $baikal_img_front = file_get_contents(base_path() . "/public/images/" . $this->data['baikal_template_front']);
        $baikal_img_back = file_get_contents(base_path() . "/public/images/" . $this->data['baikal_template_back']);
        $data['grr_front_template_base64'] = base64_encode($grr_img_front);
        $data['grr_back_template_base64'] = base64_encode($grr_img_back);
        $data['baikal_front_template_base64'] = base64_encode($baikal_img_front);
        $data['baikal_back_template_base64'] = base64_encode($baikal_img_back);
        return response()->json($data, 200);
    }
}
