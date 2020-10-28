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
    </style>
    <div class="content-wrapper">
		<!-- Content Header (Page header) -->
		<section class="content-header">
			<h1>
				Create
				<small>Employee</small>
			</h1>
			<ol class="breadcrumb">
				<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
				<li class="active">Create Employee</li>
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
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="nb_team">Team Number</label>
                                                    <input type="text" class="form-control" id="nb_team" name="nb_team" placeholder="1, 2, 3..." required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="department">Department</label>
                                                <select id=department" name="id_department" class="form-control">
                                                    @foreach($departments as $id_department => $department)
                                                        <option value="{{$id_department}}">{{$department}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="col-form-label col-md-5 pt-0">Is this employee manager?</label>
                                            <div class="col-md-6">
                                                <label class="radio-inline">
                                                    <input type="radio" name="is_manager" id="is_manager" value="1"> Yes
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" name="is_manager" id="is_manager" value="0" checked> No
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button id="submit" class="btn btn-default bg-purple btn-lg">Submit</button>
                                </div>
                            </form>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>
            </div>
            <!-- /.row -->
        </section>
		<!-- Main content -->

	</div>

	<script>
		$( document ).ready(function() {
			$("#mainEmployeeNav").addClass('active');
			$("#createEmployeeNav").addClass('active');
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

        $('#submit').on('click', function(e) {
            //e.preventDefault();
            let formData = new FormData($('form')[0]);
            console.log(formData);
            $.ajax({
                url: '/employee/create-employee',
                method: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('button').attr('disabled', true);
                },
                success: function (response, textStatus, xhr) {
                    let message = '';
                    if (xhr.status === 200) {
                        message = 'Employee was successfully created!';
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
            });
        });

	</script>
@endsection
