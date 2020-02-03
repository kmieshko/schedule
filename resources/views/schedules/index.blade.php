@section('body')
    <style>
        .w-12 {
            width: 12%;
        }
        .weekend {
            background-color: chocolate;
            opacity: 0.8;
        }
        table, table th {
            border: solid #636b6f 2px !important;
        }
        .employee-name {
            border-left: solid #636b6f 2px !important;
            border-right: solid #636b6f 2px !important;
        }
        .weekend-name {
            border-top: solid #636b6f 2px !important;
            border-bottom: solid #636b6f 2px !important;
        }
    </style>

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
							@foreach($schedules as $nb_week => $employees)
								<table id="W{{$nb_week}}" class="table table-bordered">
                                    <thread>
                                        <tr>
                                            <th rowspan="3"></th>
                                            <th  colspan="7" class="text-center">
                                                {{'WEEK #' . $nb_week}}
                                            </th>
                                        </tr>
                                        <tr>
                                            @foreach($weekends as $weekend)
                                                <td class="w-12 weekend-name text-center text-capitalize">{{$weekend}}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach($weekends as $key => $weekend)
                                                <td class="w-12 weekend-name text-center text-capitalize">{{date('m/d/Y', strtotime($weeks[$nb_week]['week_start'] . '+'.$key.' days'))}}</td>
                                            @endforeach
                                        </tr>
                                    </thread>
									@foreach($employees as $id_employee => $employee)
										<tr id="{{$employee['id_employee']}}">
											<td class="employee-name">{{$employee['last_name'] . ' ' . $employee['first_name']}}</td>
											@foreach($weekends as $weekend)
												<td class="weekend {{$weekend}} @if($employee[$weekend]) {{'bg-warning'}} @endif"></td>
											@endforeach
										</tr>
									@endforeach
								</table>
                                <br/>
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
