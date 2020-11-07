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
                            <div id="form">
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
                                                    <input type="radio" name="template" value="grr"> GRR
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" name="template" value="baikal" checked> Baikal
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button id="generate" class="btn btn-default bg-purple btn-lg">Generate ID</button>
                                </div>
                            </div>
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
                        message = 'Templates received!';
                        grr_front_template.src = 'data:image/png;base64,' + response.grr_front_template_base64;
                        grr_back_template.src = 'data:image/png;base64,' + response.grr_back_template_base64;
                        baikal_front_template.src = 'data:image/png;base64,' + response.baikal_front_template_base64;
                        baikal_back_template.src = 'data:image/png;base64,' + response.baikal_back_template_base64;
                    } else {
                        message = 'Error receiving templates! Try again later';
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
            let image_employee = $('#imgPreview')[0];
            let employeeImgPath = image_employee.src;
            obj['image_employee'] = image_employee;
            obj['employeeImgPath'] = employeeImgPath;
        });

        /**********ID-CARD START***************/

        let obj = {
            employeeName: '',
            employeePosition: '',
            image_employee: '',
            id_card: '',
            employeeImgPath: '',
            template: ''
        };

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
                xlage: 350,
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
            let qr = "http://chart.apis.google.com/chart?cht=qr&chl=" + data + "&chs=" + size.xlage;
            if (obj.template === 'baikal') {
                qr += '&chco=283371';
            }
            return qr;
        }

        function createIdCard(front_canvas, back_canvas, front_context, back_context, obj)
        {
            let emplPhotoSize = 256;

            // Draw Front Template
            if (obj.template === 'grr') {
                front_context.drawImage(grr_front_template, 0, 0);
            } else {
                front_context.drawImage(baikal_front_template, 0, 0);
            }

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
            if (obj.template === 'baikal') {
                front_context.fillStyle = "#ffffff";
            }
            for (let i = 0; i < lines.length; i++) {
                front_context.fillText(lines[i], x, y + (i * lineHeight));
            }

            // Draw Back Template
            if (obj.template === 'grr') {
                back_context.drawImage(grr_back_template, 0, 0);
            } else {
                back_context.drawImage(baikal_back_template, 0, 0);
            }

            // Draw QR Code on Back Canvas
            let qr = new Image();
            qr.setAttribute('crossOrigin', 'anonymous');
            qr.onload = function() {
                if (obj.template === 'grr') {
                    back_context.drawImage(qr, 530, 110);
                } else {
                    back_context.drawImage(qr, 530, 215);
                }
                let res_img_front = convertCanvasToImage(front_canvas);
                let res_img_back = convertCanvasToImage(back_canvas);
                $('#idCardModal').modal('show');
            };
            qr.src = generateQrCode(obj);
        }

        $('#generate').on('click', function(e){
            let employeeName = $('#firstName').val() + ' ' + $('#lastName').val();
            let employeePosition = $('#position').val();
            let template = $('input[name=template]:checked').val();
            let d = new Date();
            let id_card = (d.getTime() + d.getTimezoneOffset() * 60 * 1000);
            obj['employeeName'] = employeeName;
            obj['employeePosition'] = employeePosition;
            obj['id_card'] = id_card;
            obj['template'] = template;

            // clear canvas
            let front_canvas =  $('#canvas-upload-front')[0];
            let back_canvas =  $('#canvas-upload-back')[0];
            let front_context = front_canvas.getContext('2d');
            let back_context = back_canvas.getContext('2d');
            front_context.clearRect(0, 0, front_canvas.width, front_canvas.height);
            back_context.clearRect(0, 0, back_canvas.width, back_canvas.height);

            console.log(obj);

            if (obj.employeeName && obj.employeePosition && obj.employeeImgPath) {
                createIdCard(front_canvas, back_canvas, front_context, back_context, obj);
            }
        });

        /**********ID-CARD END***************/

	</script>
@endsection
