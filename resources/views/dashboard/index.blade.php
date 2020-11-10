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
                <div class="col-md-12">
                    <div class="box box-purple p-5">
                        <div class="box-header">
                            <h3 class="box-title">Dashboard</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">


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
        });
    </script>
@endsection
