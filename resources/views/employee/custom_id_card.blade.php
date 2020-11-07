@section('body')
    <style>
        .p-5 {
            padding: 5px;
        }

        .bg-purple {
            white-space: normal !important;
        }

        .employee-photo {
            max-height: 256px;
            margin: 0 auto;
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

        div canvas {
            width: 100%;
            border: 4px solid #283371;
        }
    </style>
    <div class="content-wrapper">
		<!-- Content Header (Page header) -->
		<section class="content-header">
			<h1>
				Generate
				<small>ID Card</small>
			</h1>
			<ol class="breadcrumb">
				<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
				<li class="active">Create ID Card</li>
			</ol>
		</section>
        <section class="content">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Employee's Info</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <form id="form">
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
                                        <div class="row">
                                            <label class="col-form-label col-md-5 pt-0">Template</label>
                                            <div class="col-md-6">
                                                <label class="radio-inline">
                                                    <input type="radio" name="template" id="template" value="grr"> GRR
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" name="template" id="template" value="baikal" checked> Baikal
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button id="generate" class="btn btn-default bg-purple btn-lg">Generate ID</button>
                                </div>
                            </form>
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

        </section>
		<!-- Main content -->

	</div>

	<script>
        let grr_front_template = new Image();
        let grr_back_template = new Image();
        let baikal_front_template = new Image();
        let baikal_back_template = new Image();

        $(document).ready(function() {
            $("#mainEmployeeNav").addClass('active');
            $("#createCustomIdCard").addClass('active');

            // GET TEMPLATES FOR ID CARD
            $.ajax({
                url: '/employee/get-card-templates',
                method: 'post',
                success: function (response, textStatus, xhr) {
                    let message = '';
                    if (xhr.status === 200) {
                        message = 'Template received!';
                        grr_front_template.src = 'data:image/png;base64,' + response.grr_front_template_base64;
                        grr_back_template.src = 'data:image/png;base64,' + response.grr_back_template_base64;
                        baikal_front_template.src = 'data:image/png;base64,' + response.baikal_front_template_base64;
                        baikal_back_template.src = 'data:image/png;base64,' + response.baikal_back_template_base64;
                    } else {
                        message = 'Error receiving template! Try again later';
                    }
                    console.log(message);
                },
            });
        });

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
                "%0A%0A";
            if (obj.template === 'grr') {
                data += "GRR Cooling Experts Inc.%0A" +
                    "159 20th Street, Brooklyn, 11232%0A%0A" +
                    "718-768-3740%0A";
            } else {
                data += "Baikal Mechanical.%0A" +
                    "536 Columbia Street, Brooklyn, 11231%0A%0A" +
                    "718-499-7200%0A";
            }
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



        // $('#generate').on('click', function(e) {
        //     //e.preventDefault();
        //     let formData = new FormData($('form')[0]);
        //     console.log(formData);
        //     $.ajax({
        //         url: '/employee/create-custom-id-card',
        //         method: 'POST',
        //         data: formData,
        //         cache: false,
        //         contentType: false,
        //         processData: false,
        //         beforeSend: function () {
        //             $('button').attr('disabled', true);
        //         },
        //         success: function (response, textStatus, xhr) {
        //             let message = '';
        //             if (xhr.status === 200) {
        //                 message = 'Employee was successfully created!';
        //             } else {
        //                 message = 'Error! Try again later';
        //             }
        //             alert(message);
        //             console.log(message);
        //         },
        //         error: function (xhr, ajaxOptions, thrownError) {
        //             alert(thrownError);
        //         },
        //         complete: function () {
        //             $('button').attr('disabled', false);
        //         }
        //     });
        // });

	</script>
@endsection
