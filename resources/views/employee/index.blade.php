@section('body')

    <style>
        .box-body {
            overflow-x: auto;
        }
        .p-5 {
            padding: 5px;
        }
        .bg-purple {
            white-space: normal !important;
        }
        .small-image {
            height: 64px;
            width: 64px;
        }
        .invisible {
            display: none;
        }
        .id-card:enabled {
            border: 2px solid #605ca8;
            color: #605ca8;
            background-color: white;
        }
        .id-card:disabled, .id-card:disabled:hover {
            border: 2px solid #b0b4c7;
            color: #818496;
            background-color: #dfe5f9;
        }
        .tooltip-id-card {
            position: relative;
            display: inline-block;
            border-bottom: 1px dotted black;
        }

        .tooltip-id-card .tooltip-id-card-text {
            visibility: hidden;
            width: 120px;
            background-color: black;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px 0;

            /* Position the tooltip */
            position: absolute;
            z-index: 1;

            top: 110%;
            left: 50%;
            margin-left: -60px; /* Use half of the width (120/2 = 60), to center the tooltip */
        }

        .tooltip-id-card:hover .tooltip-id-card-text {
            visibility: visible;
        }

        #idCardModal .modal-content {
            /*height: 638px;*/
            /*width: 1013px;*/
        }

        div canvas {
            width: 100%;
            border: 4px solid #283371;
        }

        .upload-btn-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            margin: 20px 0;
        }

        .btn-upload {
            border: 2px solid #605ca8;
            color: #605ca8;
            background-color: white;
        }

        .upload-btn-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
        }

        .loader {
            border: 16px solid #9e9aff; /* Light grey */
            border-top: 16px solid #605ca8; /* Blue */
            border-radius: 50%;
            width: 120px;
            height: 120px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                View
                <small>Employees</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">View Employees</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-purple p-5">
                        <div class="box-header">
                            <h3 class="box-title">Employees</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <table id="employees" class="table table-bordered table-striped" data-show-toggle="true" data-show-columns="true" data-show-multi-sort="true">
                                <thead>
                                <tr>
                                    <th data-sortable="true">#</th>
                                    <th class="text-center">Photo</th>
                                    <th data-sortable="true">Name</th>
                                    <th data-sortable="true">Post</th>
                                    <th data-sortable="true">Team</th>
                                    <th data-sortable="true">Department</th>
                                    <th data-sortable="true">Position</th>
                                    <th data-sortable="true">ID Card</th>
                                    <th class="text-center">Change</th>
                                    <th class="text-center">Delete</th>
                                </tr>
                                </thead>
                                @foreach($employees as $key => $employee)
                                    <tr data-id_employee="{{$employee->id}}" class="employee">
                                        <td>{{$key + 1}}</td>
                                        <td class="text-center employee-photo">
                                            @if($employee->image)
                                                <img class="small-image employee-image" src="/public/images/employee_photo/{{$employee->image}}" alt="{{$employee->last_name . ' ' . $employee->first_name}}">
                                            @else
                                                <img class="small-image employee-image" src="/public/images/{{$default_image}}" alt="{{$employee->last_name . ' ' . $employee->first_name}}">
                                            @endif
                                        </td>
                                        <td class="employee-name">{{$employee->last_name . ' ' . $employee->first_name}}</td>
                                        <td class="employee-post">
                                            @if($employee->is_manager)
                                                manager
                                            @else
                                                worker
                                            @endif
                                        </td>
                                        <td class="employee-team">{{'#' . $employee->nb_team}}</td>
                                        <td class="employee-department">{{$employee->department_name}}</td>
                                        <td class="employee-position">{{$employee->position}}</td>
                                        <td>
                                            <button class="id-card btn tooltip-id-card"
                                                    data-id_employee="{{$employee->id}}"
                                                    data-id_card="{{$employee->id_card}}"
                                                    {{($employee->image) ? '' : 'disabled'}}
                                                    data-toggle="modal"
                                                    data-target="#idCardModal"
                                            >
                                                ID CARD
                                                @if(!$employee->id_card)
                                                    <span class="tooltip-id-card-text">Photo needed</span>
                                                @endif

                                            </button>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-md bg-purple change-employee"
                                                    data-id_employee="{{$employee->id}}"
                                            >
                                                Change
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-md btn-danger delete-employee" data-id_employee="{{$employee->id}}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>
            </div>
            <!-- /.row -->


            <!-- Modal ID-card-->
            <div class="modal fade" id="idCardModal" tabindex="-1" role="dialog">
                <div class="modal-dialog " role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">Employee's ID-card</h4>
                        </div>
                        <div class="modal-body">
                            <div class="front-canvas-block">
                                <canvas id="canvas-upload-front" height="638" width="1013" class=""></canvas>
                            </div>
                            <div class="black-canvas-block">
                                <canvas id="canvas-upload-back" height="638" width="1013" class=""></canvas>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Change Employee-->
            <div class="modal fade" id="changeEmployeeModal" tabindex="-1" role="dialog">
                <div class="modal-dialog " role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">Change employee info</h4>
                        </div>
                        <div class="modal-body">
                            <form id="form-employee-info">
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <div class="text-center">
                                                <img id="imgPreview" src="/public/images/user.png" alt="user photo" class="img-responsive employee-photo">
                                                <div class="upload-btn-wrapper">
                                                    <button class="btn btn-default btn-upload">Upload a file</button>
                                                    <input type='file' name="file" id="imgInput" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="invisible">
                                        <label for="id_employee">ID employee</label>
                                        <input type="text" class="form-control" id="id_employee" name="id_employee">
                                    </div>
                                    <div class="col-md-7">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="firstName">First Name</label>
                                                    <input type="text" class="form-control" id="firstName" name="first_name" placeholder="John" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="lastName">Last Name</label>
                                                    <input type="text" class="form-control" id="lastName" name="last_name" placeholder="Doe" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="position">Position</label>
                                            <input type="text" class="form-control" id="position" name="position" placeholder="Mechanic" required>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button id="close-changes" type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button id="save-changes" type="button" class="btn bg-purple">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script>

        let front_template = new Image();
        let back_template = new Image();

        $(document).ready(function() {
            $("#employees").DataTable({
                "pageLength": 25
            });
            $("#mainEmployeeNav").addClass('active');
            $("#viewEmployeeNav").addClass('active');

            // GET TEMPLATES FOR ID CARD
            $.ajax({
                url: '/employee/get-grr-card-template',
                method: 'post',
                success: function (response, textStatus, xhr) {
                    let message = '';
                    if (xhr.status === 200) {
                        message = 'Template received!';
                        front_template.src = 'data:image/png;base64,' + response.front_template_base64;
                        back_template.src = 'data:image/png;base64,' + response.back_template_base64;
                    } else {
                        message = 'Error receiving template! Try again later';
                    }
                    console.log(message);
                },
            });
        });

        /************DELETE EMPLOYEE START***********/

        $('.delete-employee').on('click', function (e) {
            let data = $(e.target).data();
            $.ajax({
                url: '/employee/delete-employee',
                method: 'POST',
                data: data,
                beforeSend: function () {
                    $('.delete-employee').attr('disabled', true);
                },
                success: function (response, textStatus, xhr) {
                    let message = '';
                    if (xhr.status === 200) {
                        message = 'Employee was successfully deleted!';
                        if (response.new_manager) {
                            message += ' ' + response.new_manager + ' is a new manager of team';
                        }
                    } else {
                        message = 'Error! Try again later';
                    }
                    alert(message);
                    console.log(message);
                    location.reload();
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError);
                },
                complete: function () {
                    $('.delete-employee').attr('disabled', false);
                }
            });
        });

        /**********DELETE EMPLOYEE END***************/


        /**********ID-CARD START***************/

        function convertCanvasToImage(canvas) {
            let new_image = new Image();
            new_image.src = canvas.toDataURL("image/png");
            return new_image;
        }

        function generateQrCode(obj)
        {
            $('#qr-code').empty();
            let size = {
                xsmall: 75,
                small: 155,
                medium: 186,
                large: 248,
                xlage: 375,
                xxlage: 450,
            };
            let data = "ID " + obj.id_card +
                "%0A" +
                obj.employeeName +
                "%0A" +
                obj.employeePosition +
                "%0A%0A" +
                "GRR Cooling Experts Inc.%0A" +
                "159 20th Street, Brooklyn, 11232%0A%0A" +
                "718-768-3740%0A";
            return "http://chart.apis.google.com/chart?cht=qr&chl=" + data + "&chs=" + size.xlage;
        }

        function createAndSaveIdCard(front_canvas, back_canvas, front_context, back_context, obj)
        {
            let emplPhotoSize = 256;

            // Draw Front Template
            front_context.drawImage(front_template, 0, 0);

            // Draw employee photo
            front_context.drawImage(obj.image_employee, 100, 250, emplPhotoSize, emplPhotoSize);

            //add Text to Front Canvas
            let text = "ID number  " + obj.id_card + "\n\n" +
                obj.employeeName + "\n" +
                obj.employeePosition + "\n";
            let x = 450;
            let y = 300;
            let lineHeight = 60;
            let lines = text.split('\n');
            front_context.font = "40px sans-serif";
            for (let i = 0; i < lines.length; i++) {
                front_context.fillText(lines[i], x, y + (i * lineHeight));
            }

            // Draw Back Template
            back_context.drawImage(back_template, 0, 0);

            // Draw QR Code on Back Canvas
            let qr = new Image();
            qr.setAttribute('crossOrigin', 'anonymous');
            qr.onload = function() {
                back_context.drawImage(qr, 530, 110);

                let res_img_front = convertCanvasToImage(front_canvas);
                let res_img_back = convertCanvasToImage(back_canvas);

                let send = {
                    image_front: res_img_front.src,
                    image_back: res_img_back.src,
                    id_card: obj.id_card,
                    id_employee: obj.id_employee
                };

                // Save Id Card
                $.ajax({
                    url: '/employee/save-id-card',
                    method: 'post',
                    contentType: 'application/x-www-form-urlencoded',
                    data: send,
                    success: function (response, textStatus, xhr) {
                        let message = '';
                        if (xhr.status === 200) {
                            message = 'Success';
                            $('#idCardModal').modal('show');
                        } else {
                            message = 'Error!';
                        }
                        console.log(message);
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        alert(thrownError);
                    },
                });
            };
            qr.src = generateQrCode(obj);
        }

        $('.id-card').on('click', function(e){
            e.preventDefault();
            let target = $(e.target);
            let id_employee = target.data().id_employee;
            let data = target.parents('.employee');
            let employeeName = data.children('.employee-name').text();
            let employeePosition = data.children('.employee-position').text();
            let image_employee = data.find('.employee-image')[0];
            let employeeImgPath = data.find('.employee-image').attr('src');
            let d = new Date();
            let id_card = (d.getTime() + d.getTimezoneOffset() * 60 * 1000);
            let obj = {
                employeeName: employeeName,
                employeePosition: employeePosition,
                image_employee: image_employee,
                id_card: id_card,
                id_employee: id_employee,
                employeeImgPath: employeeImgPath
            };

            $.ajax({
                url: '/employee/get-id-card',
                method: 'POST',
                contentType: 'application/x-www-form-urlencoded',
                data: {id: obj.id_employee},
                beforeSend: function() {
                    $('button.change-employee').attr('disabled', true);
                    $('button.delete-employee').attr('disabled', true);

                    // clear canvas
                    let front_canvas =  $('#canvas-upload-front')[0];
                    let back_canvas =  $('#canvas-upload-back')[0];
                    let front_context = front_canvas.getContext('2d');
                    let back_context = back_canvas.getContext('2d');
                    front_context.clearRect(0, 0, front_canvas.width, front_canvas.height);
                    back_context.clearRect(0, 0, back_canvas.width, back_canvas.height);
                },
                success: function (response, textStatus, xhr) {
                    let front_canvas =  $('#canvas-upload-front')[0];
                    let back_canvas =  $('#canvas-upload-back')[0];
                    let front_context = front_canvas.getContext('2d');
                    let back_context = back_canvas.getContext('2d');
                    front_context.clearRect(0, 0, front_canvas.width, front_canvas.height);
                    back_context.clearRect(0, 0, back_canvas.width, back_canvas.height);
                    if (xhr.status === 200) {
                        let front = new Image();
                        let back = new Image();
                        front.src = response.front_id_card;
                        back.src = response.back_id_card;
                        front.onload = function(){ front_context.drawImage(front, 0, 0); };
                        back.onload = function(){ back_context.drawImage(back, 0, 0); };
                        $('#idCardModal').modal('show');
                        console.log(front);
                    } else if (xhr.status === 204) {
                        console.log('ID-card Not found. Creating new ID-card');
                        createAndSaveIdCard(front_canvas, back_canvas, front_context, back_context, obj);
                    } else {
                        alert('Error creating ID-card! Reload page and try again!');
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError+1);
                },
                complete: function () {
                    $('button.change-employee').attr('disabled', false);
                    $('button.delete-employee').attr('disabled', false);
                }
            });
        });

        /**********ID-CARD END***************/


        /**********CHANGE EMPLOYEE START***************/

        function readURL(input) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();

                reader.onload = function(e) {
                    $('#imgPreview').attr('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]); // convert to base64 string
            }
        }

        $("#imgInput").change(function() {
            readURL(this);
        });

        $('#close-changes').on('click', function () {
            $("#imgInput").val('');
        });

        $('.change-employee').on('click', function (e) {
            e.preventDefault();
            let target = $(e.target);
            let data = target.parents('.employee');
            let id_employee = target.data().id_employee;
            let employeeName = data.children('.employee-name').text().split(' ');
            let employeePosition = data.children('.employee-position').text();
            let employeeImage = data.children('.employee-photo').find('.employee-image')[0].src;
            console.log(employeeImage);
            $('#imgPreview').attr("src", employeeImage);
            $('#changeEmployeeModal').modal('show');
            $('#lastName').val(employeeName[0]);
            $('#firstName').val(employeeName[1]);
            $('#position').val(employeePosition);
            $('#id_employee').val(id_employee);
        });

        $('#save-changes').on('click', function (e) {
            e.preventDefault();
            let formData = new FormData($('#form-employee-info')[0]);
            $.ajax({
                url: '/employee/save-changes',
                method: 'post',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response, textStatus, xhr) {
                    if (xhr.status === 200) {
                        alert('Success!');
                    } else {
                        alert('Error! Try again');
                    }
                    location.reload();
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError);
                }
            });
        });

        /**********CHANGE EMPLOYEE END***************/
    </script>
@endsection
