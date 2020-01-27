<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Schedule extends Model
{
    public function getLatestSchedule($n = 1)
    {
        $latest_week = DB::table("schedules")
            ->where('id_week', '=', DB::raw("(SELECT MAX(schedules.id_week) FROM schedules)"))
            ->get()
            ->toArray();
        return $latest_week;
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
