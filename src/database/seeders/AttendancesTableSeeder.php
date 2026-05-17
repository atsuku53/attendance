<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Rest;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userIds = DB::table('users')->pluck('id');

        $months = [
            ['start' => '2026-02-01', 'end' => '2026-02-28'],
            ['start' => '2026-03-01', 'end' => '2026-03-31'],
        ];

        foreach ($months as $month) {
            $current = Carbon::parse($month['start']);
            $end     = Carbon::parse($month['end']);

            while ($current->lte($end)) {
                if (!$current->isWeekend()) {
                    foreach ($userIds as $userId) {
                        $attendStart = $current->copy()->setTime(8, 45, 0);
                        $attendEnd   = $current->copy()->setTime(17, 15, 0);

                        $attendance = Attendance::create([
                            'user_id'      => $userId,
                            'attend_start' => $attendStart,
                            'attend_end'   => $attendEnd,
                        ]);

                        Rest::create([
                            'attendance_id' => $attendance->id,
                            'rest_start'    => $current->copy()->setTime(12, 0, 0),
                            'rest_end'      => $current->copy()->setTime(13, 0, 0),
                        ]);
                    }
                }

                $current->addDay();
            }
        }
    }
}
