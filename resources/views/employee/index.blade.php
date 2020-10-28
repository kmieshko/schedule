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
        .small-image {
            height: 64px;
            width: 64px;
        }
    </style>
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                View
                <small>Employees</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">View Employees</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="box box-purple p-5">
                        <div class="box-header">
                            <h3 class="box-title">Employees</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <table id="employees" class="table table-bordered table-striped" data-show-toggle="true" data-show-columns="true" data-show-multi-sort="true">
                                <thead>
                                <tr>
                                    <th data-sortable="true">#</th>
                                    <th class="text-center">Photo</th>
                                    <th data-sortable="true">Name</th>
                                    <th data-sortable="true">Post</th>
                                    <th data-sortable="true">Team</th>
                                    <th data-sortable="true">Department</th>
                                    <th data-sortable="true">Position</th>
                                    <th data-sortable="true" class="text-center">Change</th>
                                    <th class="text-center" data-sortable="true">Delete</th>
                                </tr>
                                </thead>
                                @foreach($employees as $key => $employee)
                                    <tr data-id="{{$employee->id}}" class="employee">
                                        <td>{{$key + 1}}</td>
                                        <td class="text-center">
                                            @if($employee->image)
                                            <img class="small-image" src="/public/images/{{$employee->image}}" alt="{{$employee->last_name . ' ' . $employee->first_name}}">
                                            @else
                                                <img class="small-image" src="/public/images/{{$default_image}}" alt="{{$employee->last_name . ' ' . $employee->first_name}}">
                                            @endif
                                        </td>
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
                                        <td>{{$employee->position}}</td>
                                        <td class="text-center">
                                            <button class="btn btn-md bg-purple change-employee" data-id_employee="{{$employee->id}}">
                                                Change
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-md btn-danger delete-employee" data-id_employee="{{$employee->id}}">
                                                Delete
                                            </button>
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
        $(document).ready(function() {
            $("#employees").DataTable({
                "pageLength": 25
            });
            $(document).ready(function() {
                $("#mainEmployeeNav").addClass('active');
                $("#viewEmployeeNav").addClass('active');
            });
        });

        $('.delete-employee').on('click', function (e) {
            let data = $(e.target).data();
            $.ajax({
                url: '/employee/delete-employee',
                method: 'POST',
                data: data,
                beforeSend: function () {
                    $('button').attr('disabled', true);
                },
                success: function (response, textStatus, xhr) {
                    edit_schedule = {};
                    let message = '';
                    if (xhr.status === 200) {
                        message = 'Employee was successfully deleted!';
                        if (response.new_manager) {
                            message += ' ' + response.new_manager + ' is a new manager of team';
                        }
                        console.log('Employee was successfully deleted');
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
            })
        });
    </script>
@endsection
