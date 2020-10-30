@section('body')
    <CORSConfiguration xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
        <CORSRule>
            <AllowedOrigin>*</AllowedOrigin>
            <AllowedMethod>GET</AllowedMethod>
            <MaxAgeSeconds>3000</MaxAgeSeconds>
        </CORSRule>
    </CORSConfiguration>

    <style>
        .pretty.p-switch.p-fill input:checked~.state:before {
            border-color: #605ca8;
            background-color: #605ca8 !important;
        }
        .pretty.p-switch .state label:after {
            background-color: #605ca8 !important;
        }
        .box-body {
            overflow-x: auto;
        }
        .p-5 {
            padding: 5px;
        }
        .row {
            margin: 0 !important;
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
                <div class="col-md-10 col-md-offset-1">
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
                                                <img class="small-image employee-image" src="/public/images/{{$employee->image}}" alt="{{$employee->last_name . ' ' . $employee->first_name}}">
                                            @else
                                                <img class="small-image employee-image" src="/public/images/{{$default_image}}" alt="{{$employee->last_name . ' ' . $employee->first_name}}">
                                            @endif
                                        </td>
                                        <td class="employee-name">{{$employee->last_name . ' ' . $employee->first_name}}</td>
                                        <td>
                                            @if($employee->is_manager)
                                                <span>Manager</span>
                                            @else
                                                <span>Worker</span>
                                            @endif
                                        </td>
                                        <td>{{'#' . $employee->nb_team}}</td>
                                        <td>{{$employee->department_name}}</td>
                                        <td class="employee-position">{{$employee->position}}</td>
                                        <td>
                                            <button class="id-card btn btn btn-default tooltip-id-card"
                                                    data-id_employee="{{$employee->id}}"
                                                    data-id_card="{{$employee->id_card}}"
                                                {{($employee->image) ? '' : 'disabled'}}>
                                                ID CARD
                                                @if(!$employee->id_card)
                                                <span class="tooltip-id-card-text">Photo needed</span>
                                                @endif

                                            </button>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-md bg-purple change-employee" data-id_employee="{{$employee->id}}">
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

            <canvas id="canvas-upload-front" height="638" width="1013" class=""></canvas>
            <canvas id="canvas-upload-back" height="638" width="1013" class=""></canvas>
        </section>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropper/4.1.0/cropper.min.js" integrity="sha512-E+gDQcIvNXE60SjCS38ysf1mGh4ObBpKcUOp0oEaHQHQAdaN2p7GelOpgEdpTuCLoIJyLkNXiqFZbyD9Ak/Ygw==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cropper/1.0.1/jquery-cropper.min.js" integrity="sha512-V8cSoC5qfk40d43a+VhrTEPf8G9dfWlEJgvLSiq2T2BmgGRmZzB8dGe7XAABQrWj3sEfrR5xjYICTY4eJr76QQ==" crossorigin="anonymous"></script>
    <script>

        let front_template = new Image();
        let back_template = new Image();
        $(document).ready(function() {
            $("#employees").DataTable({
                "pageLength": 25
            });
            $("#mainEmployeeNav").addClass('active');
            $("#viewEmployeeNav").addClass('active');

            $.ajax({
                url: '/employee/get-card-template',
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

        $('.delete-employee').on('click', function (e) {
            let data = $(e.target).data();
            $.ajax({
                url: '/employee/delete-employee',
                method: 'POST',
                data: data,
                beforeSend: function () {
                    $('button').attr('disabled', true);
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
                    $('button').attr('disabled', false);
                }
            })
        });


        /***********************ID CARD************************/

        function ScaleImage(srcwidth, srcheight, targetwidth, targetheight, fLetterBox) {

            var result = {width: 0, height: 0, fScaleToTargetWidth: true};
            if ((srcwidth <= 0) || (srcheight <= 0) || (targetwidth <= 0) || (targetheight <= 0)) {
                return result;
            }
            var scaleX1 = targetwidth;
            var scaleY1 = (srcheight * targetwidth) / srcwidth;
            var scaleX2 = (srcwidth * targetheight) / srcheight;
            var scaleY2 = targetheight;
            var fScaleOnWidth = (scaleX2 > targetwidth);
            if (fScaleOnWidth) {
                fScaleOnWidth = fLetterBox;
            }
            else {
                fScaleOnWidth = !fLetterBox;
            }

            if (fScaleOnWidth) {
                result.width = Math.floor(scaleX1);
                result.height = Math.floor(scaleY1);
                result.fScaleToTargetWidth = true;
            }
            else {
                result.width = Math.floor(scaleX2);
                result.height = Math.floor(scaleY2);
                result.fScaleToTargetWidth = false;
            }
            result.targetleft = Math.floor((targetwidth - result.width) / 2);
            result.targettop = Math.floor((targetheight - result.height) / 2);
            return result;
        }

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

        $('.id-card').on('click', function(e){

            let is_id = $(e.target).parents('.employee').find('.id-card').data().id_card;
            if (is_id) return false;

            let data = $(e.target).parents('.employee');
            let employeeName = data.children('.employee-name').text();
            let employeePosition = data.children('.employee-position').text();
            let image_employee = data.find('.employee-image')[0];
            let employeeImgPath = data.find('.employee-image').attr('src');
            let id_employee = $(e.target).data().id_employee;
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

            /*********CANVAS*****************/
            let front_canvas =  document.getElementById('canvas-upload-front');
            let back_canvas =  document.getElementById('canvas-upload-back');
            let front_context = front_canvas.getContext('2d');
            let back_context = back_canvas.getContext('2d');
            front_context.clearRect(0, 0, front_canvas.width, front_canvas.height);
            back_context.clearRect(0, 0, back_canvas.width, back_canvas.height);
            let emplPhotoSize = 256;

            // Draw Front Template
            front_context.drawImage(front_template, 0, 0);

            // Draw employee photo
            front_context.drawImage(image_employee, 100, 250, emplPhotoSize, emplPhotoSize);

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
                $.ajax({
                    url: '/employee/save-id-card',
                    method: 'post',
                    contentType: 'application/x-www-form-urlencoded',
                    data: send,
                    success: function (response, textStatus, xhr) {
                        let message = '';
                        if (xhr.status === 200) {
                            message = 'Success';
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
        });
    </script>
@endsection
