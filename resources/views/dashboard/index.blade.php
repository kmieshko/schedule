@section('body')

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Dashboard
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-md-6">
                    <div class="box box-purple p-5">
                        <div class="box-header">
                            <h3 class="box-title">Dashboard</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <canvas id="myChart" width="400" height="400"></canvas>
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
            $("#mainDashboardNav").addClass('active');
            $("#viewDashboardNav").addClass('active');

            $.ajax({
                method: 'get',
                url: '/dashboard/get-dashboard-info',
                success: function (response) {
                    let labels = [];
                    let data = [];
                    let palette = [
                        '#900C3F',
                        '#C70039',
                        '#FF5733',
                        '#FF8D1A',
                        '#FFC300',
                        '#EDDD53',
                        '#ADD45C',
                        '#57C785',
                        '#00BAAD',
                        '#2A7B9B',
                        '#3D3D6B',
                        '#511849',
                    ];
                    $.each(response.tasks_info, function (key, value) {
                        labels.push(key);
                        data.push(value);
                    });
                    var config = {
                        type: 'pie',
                        data: {
                            datasets: [{
                                data: data,
                                backgroundColor: palette
                            }],
                            labels: labels
                        },
                        options: {
                            responsive: true
                        }
                    };
                    console.log(labels);
                    let ctx = document.getElementById('myChart').getContext('2d');
                    let myPieChart = new Chart(ctx, config);
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError);
                },
            })
        });
    </script>
@endsection
