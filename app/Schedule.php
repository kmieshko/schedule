<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Schedule extends Model
{
    public function getLastSchedule($n = 3)
    {
        $schedule = DB::table('schedules')
            ->limit($n)
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
        return $schedule;
    }

    public function insertSchedule($schedules)
    {
        foreach ($schedules as $week => $teams) {
            foreach ($teams as $team => $employees)
                foreach ($employees as $employee) {
                // запись расписания в БД
                    DB::table('users')
                        ->insert(
                            array(
                                'id_employee' => $employee['id'],
                                'id_week' => $week,

                            )
                        );
                }
            }
        }
}
