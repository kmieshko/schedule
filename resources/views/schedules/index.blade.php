@section('body')
    <style>
        .w-7 {
            width: 7%;
        }
        .w-12 {
            width: 12%;
        }
        .p-5 {
            padding: 5px;
        }
        .box-purple {
            border-top-color: #605ca887;
        }
        .box-body {
            overflow: auto;
        }
        .weekend {
            background-color: chocolate;
            opacity: 0.8;
        }
        table {
            overflow-x: scroll;
        }
        table, table th {
            border: solid rgb(129, 132, 150) 2px !important;
        }
        .employee-name {
            border-left: solid rgb(129, 132, 150) 2px !important;
            border-right: solid rgb(129, 132, 150) 2px !important;
        }
        .weekend-name {
            border-top: solid rgb(129, 132, 150) 2px !important;
            border-bottom: solid rgb(129, 132, 150) 2px !important;
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
						<p>
                            Current WEEK #{{$current_week}} {{date('m/d/Y', strtotime($current_week_dates['week_start'])) . ' - ' . date('m/d/Y', strtotime($current_week_dates['week_start']))}}
                        </p>
					</div>

                    @foreach($schedules as $nb_week => $employees)
                        <div class="box box-purple collapsed-box p-5">
                            <div class="box-header">
                                <h4 class="box-title">
                                    {{'WEEK #' . $nb_week . ' ' . date('m/d/Y', strtotime($weeks[$nb_week]['week_start'])) . ' - ' . date('m/d/Y', strtotime($weeks[$nb_week]['week_end']))}}
                                </h4>
                                <div class="box-tools pull-right">
                                    <button class="btn btn-default btn-sm" data-widget="collapse"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                            <div style="display: none;" class="box-body">
                                <table id="W{{$nb_week}}" class="table table-bordered">
                                    <thread>
                                        <tr>
                                            <th rowspan="3"></th>
                                            <th  colspan="7" class="text-center">
                                                {{'WEEK #' . $nb_week . ' ' . date('m/d/Y', strtotime($weeks[$nb_week]['week_start'])) . ' - ' . date('m/d/Y', strtotime($weeks[$nb_week]['week_end']))}}
                                            </th>
                                        </tr>
                                        <tr>
                                            @foreach($weekends as $weekend)
                                                <td class="w-7 weekend-name text-center text-capitalize">{{$weekend}}</td>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            @foreach($weekends as $key => $weekend)
                                                <td class="w-7 weekend-name text-center text-capitalize">{{date('m/d/Y', strtotime($weeks[$nb_week]['week_start'] . '+'.$key.' days'))}}</td>
                                            @endforeach
                                        </tr>
                                    </thread>
                                    @foreach($employees as $id_employee => $employee)
                                        <tr id="{{$employee['id_employee']}}">
                                            <td class="employee-name w-12">{{$employee['last_name'] . ' ' . $employee['first_name']}}</td>
                                            @foreach($weekends as $weekend)
                                                <td class="weekend {{$weekend}} @if($employee[$weekend]) {{'bg-yellow'}} @endif"></td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                @endforeach
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
