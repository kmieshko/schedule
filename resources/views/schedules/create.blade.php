@section('body')
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
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title">General Info</h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <table id="manageTable" class="table table-bordered table-striped">
                                    <thead>
                                    <tr>
                                        <th>Latest week</th>
                                        <td>WEEK #{{$latest_week}}</td>
                                    </tr>
                                    <tr>
                                        <th>Latest dates</th>
                                        <td>01/01/2020 - 02/02/2020</td>
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
                                <table id="manageTable" class="table table-bordered table-striped">
                                    <thead>
                                    <tr>
                                        <th>Latest week</th>
                                        <td>WEEK #{{$latest_week}}</td>
                                    </tr>
                                    <tr>
                                        <th>Latest dates</th>
                                        <td>01/01/2020 - 02/02/2020</td>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                            <!-- /.box-body -->
                        </div>
                    </div>
					<!-- /.box -->
				</div>
                <div class="col-md-6">
                    <div class="box">
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
                                </tr>
                                </thead>
                                @foreach($employees as $key => $employee)
                                    <tr id="id_employe_{{$employee->id}}">
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
	</script>
@endsection
