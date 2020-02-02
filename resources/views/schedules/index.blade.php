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

					<div id="messages">Current W <?= 'nb_week (01/01/2020 - 02-02-2020)'; ?></div>


					<div class="box">
						<div class="box-header">
							<h3 class="box-title">Select week</h3>
						</div>
						<!-- /.box-header -->
						<div class="box-body">
							<table id="manageTable" class="table table-bordered table-striped">
								<thead>
								<tr>

								</tr>
								</thead>

							</table>
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
