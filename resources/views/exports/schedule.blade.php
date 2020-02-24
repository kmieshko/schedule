<table id="W{{$nb_week}}" class="table table-bordered">
    <tr>
        <th rowspan="3"></th>
        <th colspan="7" class="text-center">
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
    @foreach($schedules as $nb_week => $employee)
    <tr id="{{$employee->id_employee}}">
        <td class="employee-name w-12">{{$employee->last_name . ' ' . $employee->first_name}}</td>
        @foreach($weekends as $weekend)
            @if($weekend == 'sunday')
                <td class="weekend {{$weekend}} bg-yellow"></td>
            @else
                <td class="weekend {{$weekend}} @if($employee->$weekend) {{'bg-yellow'}} @endif"></td>
            @endif
        @endforeach
    </tr>
    @endforeach
</table>
