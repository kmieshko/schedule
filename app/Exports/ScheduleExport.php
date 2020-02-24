<?php


namespace App\Exports;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ScheduleExport implements FromView
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
//        return view('exports.schedule', [
//            'schedules' => $this->data['schedules'],
//            'weeks' => $this->data['weeks'],
//            'weekends' => $this->data['weekends'],
//        ]);
//        dd($this->data);
        return view('exports.schedule', $this->data);
    }
}
