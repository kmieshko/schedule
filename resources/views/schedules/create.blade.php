@section('body')
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
    </style>
    <div class="content-wrapper">
		<!-- Content Header (Page header) -->
		<section class="content-header">
			<h1>
				Create
				<small>Schedule</small>
			</h1>
			<ol class="breadcrumb">
				<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
				<li class="active">Schedule</li>
			</ol>
		</section>

		<!-- Main content -->
		<section class="content">
			<!-- Small boxes (Stat box) -->
			<div class="row">
				<div class="col-md-3">
					<div>
                        <div class="box p-5">
                            <div class="box-header">
                                <h3 class="box-title">General Info</h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <table id="manageTable" class="table table-bordered table-striped">
                                    <thead>
                                    <tr>
                                        <th>Latest week</th>
                                        <td>WEEK #<span id="latestWeek">{{$latest_week}}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Week start</th>
                                        <td id="weekStart">{{$week_start}}</td>
                                    </tr>
                                    <tr>
                                        <th>Week end</th>
                                        <td id="weekEnd">{{$week_end}}</td>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                            <!-- /.box-body -->
                        </div>
                    </div>
                    <div>
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title">Create Schedule</h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-7">
                                        <p>
                                            <input id="weeksAmount" class="form-control input-mg" type="text" placeholder="Weeks amount" value="1">
                                        </p>
                                    </div>
                                    <div class="col-md-5">
                                        <p>
                                            <button id="createSchedule" type="button" class="btn btn-block btn-md bg-purple">Create</button>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- /.box-body -->
                        </div>
                    </div>
					<!-- /.box -->
				</div>
                <div class="col-md-6">
                    <div class="box box-purple p-5">
                        <div class="box-header">
                            <h3 class="box-title">Check employees</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <table id="manageTable" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Post</th>
                                    <th>Team</th>
                                    <th>Department</th>
                                    <th>Scheduling</th>
                                </tr>
                                </thead>
                                @foreach($employees as $key => $employee)
                                    <tr data-id="{{$employee->id}}" class="employee">
                                        <td>{{$key + 1}}</td>
                                        <td>{{$employee->last_name . ' ' . $employee->first_name}}</td>
                                        <td>
                                            @if($employee->is_manager)
                                                <span>Manager</span>
                                            @else
                                                <span>Worker</span>
                                            @endif
                                        </td>
                                        <td>{{'#' . $employee->nb_team}}</td>
                                        <td>{{$employee->department_name}}</td>
                                        <td class="text-center">
                                            <div class="pretty p-switch p-fill purple">
                                                <input class="checkEmployee" type="checkbox" checked/>
                                                <div class="state">
                                                    <label></label>
                                                </div>
                                            </div>
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


		</section>
	</div>

	<script>
		$( document ).ready(function() {
			$("#mainScheduleNav").addClass('active');
			$("#createScheduleNav").addClass('active');
		});

        $('#weeksAmount').inputFilter(function (value) {
            return /^\d*$/.test(value);
        });

        $('#weeksAmount').on('input', function () {
            let value = $('#weeksAmount').val();
            if (value > 9) value = 9;
            else if (value < 1) value = 1;
            $('#weeksAmount').val(value);
        });

        $('#createSchedule').on('click', function () {
            let data = {};
            data['weeks_amount'] = $('#weeksAmount').val();
            data['employees'] = [];
            let employees = $('.employee .checkEmployee:not(:checked)').parents('tr');
            employees.each(function () {
                data['employees'].push($(this).data( "id" ));
            });
            $.ajax({
                url: '/schedules/create-schedule',
                method: 'POST',
                data: data,
                beforeSend: function() {
                    $('#createSchedule').attr('disabled', true);
                },
                success: function (response, textStatus, xhr) {
                    if (xhr.status === 200) {
                        alert('Success!');
                        $('#weekStart').text(response.data.week_start);
                        $('#weekEnd').text(response.data.week_end);
                        $('#latestWeek').text(response.data.latest_week);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError);
                },
                complete: function() {
                    $('#createSchedule').attr('disabled', false);
                }
            })
        });

		$('#createSchedule').on('click', function () {

        });
	</script>
@endsection
