@section('body')
	<div class="content-wrapper">
		<!-- Content Header (Page header) -->
		<section class="content-header">
			<h1>
				View
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
				<div class="col-md-12 col-xs-12">

					<div id="messages">
						Current W{{$current_week}} {{($current_week_dates['week_start'] . ' - ' . $current_week_dates['week_end'])}}
					</div>


					<div class="box">
						<div class="box-header">
							<h3 class="box-title">Select week</h3>
						</div>
						<!-- /.box-header -->
						<div class="box-body">
							{{--<table id="manageTable" class="table table-bordered table-striped">--}}
								{{--<thead>--}}
								{{--<tr>--}}

								{{--</tr>--}}
								{{--</thead>--}}

							{{--</table>--}}
							@foreach($schedules as $nb_week => $employees)
								<table id="W{{$nb_week}}" class="table table-bordered">
									<tr>
										<td colspan="8">
											{{'WEEK' . $nb_week . ' '}}
										</td>
									</tr>
									<tr>
										<td></td>
										<td>Monday</td>
										<td>Tuesday</td>
										<td>Wednesday</td>
										<td>Thursday</td>
										<td>Friday</td>
										<td>Saturday</td>
										<td>Sunday</td>
									</tr>
									@foreach($employees as $id_employee => $employee)
										<tr id="{{$employee['id_employee']}}">
											<td>{{$employee['last_name'] . ' ' . $employee['first_name']}}</td>
											@foreach($weekends as $weekend)
												<td>
													{{$employee[$weekend]}}
												</td>
											@endforeach
										</tr>
									@endforeach
									<tr><td colspan="8"></td></tr>
								</table>
							@endforeach

						</div>
						<!-- /.box-body -->
					</div>
					<!-- /.box -->
				</div>
				<!-- col-md-12 -->
			</div>
			<!-- /.row -->


		</section>
	</div>

	<script>
		$( document ).ready(function() {
			$("#mainScheduleNav").addClass('active');
			$("#viewScheduleNav").addClass('active');
		});
	</script>
@endsection
