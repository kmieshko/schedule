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
		.cursor:hover {
			cursor: pointer;
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
		.modal-dialog {
			width: 80% !important;
			overflow: auto !important;
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
                        <div data-id_week="{{$nb_week}}" class="box box-purple collapsed-box p-5 cursor show-schedule" data-toggle="modal">
                            <div class="box-header">
                                <h4 class="box-title">
                                    WEEK #{{$nb_week}}
									<span class="week-start">
										{{date('m/d/Y', strtotime($weeks[$nb_week]['week_start']))}}
									</span> -
									<span class="week-end">
										{{date('m/d/Y', strtotime($weeks[$nb_week]['week_end']))}}
									</span>
                                </h4>
							</div>
                        </div>
                @endforeach
					<!-- /.box -->
				</div>
				<!-- col-md-12 -->
			</div>
			<!-- /.row -->

			<!-- Modal -->
			<div class="modal fade" id="modal-schedule" tabindex="-1" role="dialog">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title">Schedule</h4>
						</div>
						<div class="modal-body">
						</div>
						<div class="modal-footer">
							<button data-id_week="{{$nb_week}}" class="btn btn-default download-schedule">Excel</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
        </section>
	</div>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-dateFormat/1.0/jquery.dateFormat.min.js" integrity="sha256-YVu3IT7nGTfxru7MQiv/TgOnffsbPuvXHRXuw1KzxWc=" crossorigin="anonymous"></script>
	<script>
		$(document).ready(function() {
			$("#mainScheduleNav").addClass('active');
			$("#viewScheduleNav").addClass('active');
		});

		$('.download-schedule').on('click', function (e) {
			let id_week = $(e.target).data("id_week");
			let data = {};
			data['id_week'] = id_week;
			$.ajax({
				url: '/schedules/download-schedule',
				method: 'POST',
				data: data,
				beforeSend: function () {
					$('button').attr('disabled', true);
				},
				success: function (response, textStatus, xhr) {
					if (xhr.status === 200) {
						window.location.href = '/schedules/download-schedule-excel';
					}
				},
				error: function (xhr, ajaxOptions, thrownError) {
					alert(thrownError);
				},
				complete: function () {
					$('button').attr('disabled', false);
				}
			})
		});

		$('.show-schedule').on('click', function () {
			let id_week = $(this).data("id_week");
			let data = {};
			data['id_week'] = id_week;
			$.ajax({
				url: '/schedules/schedule-by-week',
				method: 'post',
				data: data,
				beforeSend: function () {
					$('.modal-body').empty();
					$('button').attr('disabled', true);
				},
				success: function (response, textStatus, xhr) {
					if (xhr.status === 200) {
						let table = createScheduleTable(response);
						$('.modal-body').append(table);
						$('#modal-schedule').modal('show');
//						window.location.href = '/schedules/download-schedule-excel';
					} else {
						alert(textStatus);
					}
				},
				error: function (xhr, ajaxOptions, thrownError) {
					alert(thrownError);
				},
				complete: function () {
					$('button').attr('disabled', false);
				}
			})
		});

		function createScheduleTable(data) {
			let schedules = data.schedules;
			let weekends = data.weekends;
			let weeks = data.weeks;
			let week_start = data.week_start;
			let week_end = data.week_end;
			let block = '';
			$.each(schedules, function (nb_week, employees) {
				block += '<table id="W' + nb_week + '" class="table table-bordered">' +
					'<thread>' +
					'<tr>' +
					'<th rowspan="3"></th>' +
					'<th  colspan="7" class="text-center">' +
					'WEEK #' + nb_week + ' ' + week_start + ' - ' + week_end + '' +
					'</th>' +
					'</tr>' +
					'<tr>';
				$.each(weekends, function (key, weekend) {
					block += '<td class="w-7 weekend-name text-center text-capitalize">' + weekend + '</td>';
				});
				block += '</tr>' +
					'<tr>';
				$.each(weekends, function (key, weekend) {
					let date = new Date(weeks[nb_week].week_start);
					date.setDate(date.getDate() + parseInt(key));
					date = $.format.date(date, "MM/dd/yyyy");
					block += '<td class="w-7 weekend-name text-center text-capitalize">' + date + '</td>';
				});
				block += '</tr>' +
					'</thread>';
				$.each(employees, function (id_employee, employee) {
					block += '<tr id="' + employee[id_employee] + '">' +
						'<td class="employee-name w-12">' + employee.last_name + ' ' + employee.first_name + '</td>';
					$.each(weekends, function (key, weekend) {
						block += '<td class="weekend ' + weekend;
						if (employee[weekend] === 1) {
							block += ' bg-yellow';
						}
						block += '"></td>';
					});
					block += '</tr>';
				});
				block += '</table>';
			});
			return block;
		}
	</script>
@endsection
