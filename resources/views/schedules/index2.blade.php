@section('body')
    <style>
        .w-7 {
            width: 7%;
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

        .table-scroll {
            overflow: auto;
        }

        thead td {
            position: -webkit-sticky; /* for Safari */
            position: sticky;
            top: 0;
            left: 1% !important;
        }
        tbody td:first-child {
            position: -webkit-sticky; /* for Safari */
            position: sticky;
            left: 0 !important;
        }


        /* To have the header in the first column stick to the left: */

        thead .empty-cell {
            left: 0 !important;
        }

        /* Just to display it nicely: */

        thead td {
            background: #000;
            color: #FFF;
            /* Ensure this stays above the emulated border right in tbody th {}: */
            z-index: 1;
        }

        tbody .employee-name, .empty-cell {
            z-index: 2;
            background: #FFF;
            border-right: 1px solid #CCC !important;
            border-left: 1px solid #CCC !important;
            /* Browsers tend to drop borders on sticky elements, so we emulate the border-right using a box-shadow to ensure it stays: */
            box-shadow: 1px 0 0 0 #ccc, -1px 0 0 0 #ccc;
        }

        table {
            border-collapse: collapse;
        }

        td,
        th {
            padding: 0.5em;
        }

        body .modal-dialog { /* Width */
            max-width: 100%;
            width: auto !important;
            display: inline-block;
        }

        .modal {
            z-index: -1;
            display: flex !important;
            justify-content: center;
        }

        .modal-open .modal {
            z-index: 1050;
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
				<li class="active">View Schedule</li>
			</ol>
		</section>

		<!-- Main content -->
		<section class="content">
			<!-- Small boxes (Stat box) -->
			<div class="row">
				<div class="col-md-12 col-xs-12">

					<div id="messages">
						<p>
                            Current WEEK #{{$current_week}} {{date('m/d/Y', strtotime($current_week_dates['week_start'])) . ' - ' . date('m/d/Y', strtotime($current_week_dates['week_end']))}}
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
				<div class="modal-dialog center">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title">Schedule</h4>
						</div>
						<div class="modal-body">
						</div>
						<div class="modal-footer">
                            <button class="btn btn-success save-changes">Save</button>
							<button class="btn btn-default download-schedule">Excel</button>
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
			let id_week = $('.table-schedule').data("id_week");
			let data = {};
			data['id_week'] = id_week;
			console.log(data);
			$.ajax({
				url: '/schedules/download-schedule',
				method: 'GET',
				data: data,
				beforeSend: function () {
					$('button').attr('disabled', true);
				},
				success: function (response, textStatus, xhr) {
					if (xhr.status === 200) {
					    window.location.href = '/schedules/download-schedule' + '?id_week=' + id_week;
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
                        $('.modal-body').css({
                            width:'auto', //probably not needed
                            height:'auto', //probably not needed
                            'max-height':'100%'
                        });
                        console.log('modal on');
						$('#modal-schedule').modal('show');
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
				block += '<div class="table-scroll"><table data-id_week="' + nb_week + '" class="table table-bordered table-schedule">' +
					'<thread>' +
					'<tr>' +
                    '<td class="empty-cell"></td>' +
					'<th  colspan="7" class="text-center">' +
					'WEEK #' + nb_week + ' ' + week_start + ' - ' + week_end + '' +
					'</th>' +
					'</tr>' +
					'<tr><td class="empty-cell"></td>';
				$.each(weekends, function (key, weekend) {
					block += '<td class="w-7 weekend-name text-center text-capitalize">' + weekend + '</td>';
				});
				block += '</tr>' +
					'<tr><td class="empty-cell"></td>';
				$.each(weekends, function (key, weekend) {
					let tmp_date = new Date(weeks[nb_week].week_start);
                    let userTimezoneOffset = tmp_date.getTimezoneOffset() * 60000;
                    let date = new Date(tmp_date.getTime() + userTimezoneOffset);
					date.setDate(date.getDate() + parseInt(key));
					date = $.format.date(date, "MM/dd/yyyy");
					block += '<td class="w-7 weekend-name text-center text-capitalize">' + date + '</td>';
				});
				block += '</tr>' +
					'</thread><tbody>';
				$.each(employees, function (id_employee, employee) {
					block += '<tr>' +
						'<td class="employee-name">' + employee.last_name + ' ' + employee.first_name + '</td>';
					$.each(weekends, function (key, weekend) {
                        edit_schedule[id_employee]={};
					    block += '<td data-id_employee="' + employee.id_employee + '" data-weekend_name="'+ weekend + '"';
						block += 'class="weekend';
						if (employee[weekend] === 1) {
							block += ' bg-yellow"';
							block += ' data-is_weekend="1"'
						} else {
                            block += '" data-is_weekend="0"'
                        }
						block += '></td>';
					});
					block += '</tr>';
				});
				block += '</tbody></table></div>';
			});
			return block;
		}

		$('.weekend').on('click', function (e) {
		    console.log(e.target);
        });

        let edit_schedule = {};
        $(document).on('click', '.weekend' , function(e) {
            $(e.target).data('is_weekend',  $(e.target).data('is_weekend') === 1 ? 0 : 1);
            let data = $(e.target).data();
            let weekend_name = data.weekend_name;
            let id_employee = data.id_employee;
            let is_weekend = data.is_weekend;
            if (is_weekend) {
                $(e.target).addClass('bg-yellow');
            } else {
                $(e.target).removeClass('bg-yellow');
            }
            if (typeof edit_schedule[id_employee][weekend_name] != 'undefined') {
                delete edit_schedule[id_employee][weekend_name];
            } else {
                edit_schedule[id_employee][weekend_name]=is_weekend;
            }
            // POST for editing in DB
            console.log(edit_schedule);
        });

        $('.save-changes').on('click', function () {
            let id_week = $('.table-schedule').data("id_week");
            $.ajax({
                url: '/schedules/save-changes',
                method: 'POST',
                data: {schedule: edit_schedule, id_week: id_week},
                beforeSend: function () {
                    $('.weekend').attr('disabled', true);
                },
                success: function (response, textStatus, xhr) {
                    edit_schedule = {};
                    if (xhr.status === 200) {
                        alert('Changes was saved');
                        console.log('Changes was saved');
                    } else {
                        alert('Everything is up to date');
                        console.log('Everything is up to date');
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError);
                },
                complete: function () {
                    $('.weekend').attr('disabled', false);
                }
            })
        })
	</script>
@endsection
