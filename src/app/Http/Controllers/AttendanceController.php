<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Administrator;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\ModifiedAttendance;
use App\Models\ModifiedRest;
use App\Http\Requests\AttendanceRequest;
use Laracsv\Export;

class AttendanceController extends Controller
{
    public function index()
    {
        $currentDate = Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日(ddd)');
        $currentTime = Carbon::now()->locale('ja')->isoFormat('HH:mm');
        $attendance = Attendance::where('user_id', auth()->id())->whereDate('attend_start', Carbon::today())->first();
        $rest = $attendance ? Rest::where('attendance_id', $attendance->id)->whereNull('rest_end')->first() : null;

        if ($attendance) {
            if ($attendance->attend_end) {
                return view('general.index_end', ['currentDate' => $currentDate, 'currentTime' => $currentTime]);
            } elseif ($rest) {
                    return view('general.index_rest', ['currentDate' => $currentDate, 'currentTime' => $currentTime]);
                }
                return view('general.index_attend', ['currentDate' => $currentDate, 'currentTime' => $currentTime]);
            }
            return view('general.index', ['currentDate' => $currentDate, 'currentTime' => $currentTime]);
    }

    public function attend_start()
    {
        $currentDate = Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日(ddd)');
        $currentTime = Carbon::now()->locale('ja')->isoFormat('HH:mm');

        Attendance::create([
            'user_id' => auth()->id(),
            'attend_start' => Carbon::now()->locale('ja'),
        ]);

        return view('general.index_attend', ['currentDate' => $currentDate, 'currentTime' => $currentTime]);
    }

    public function attend_end(Request $request)
    {
        $currentDate = Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日(ddd)');
        $currentTime = Carbon::now()->locale('ja')->isoFormat('HH:mm');
        $attendance = Attendance::where('user_id', auth()->id())->whereDate('attend_start', Carbon::today())->first();
        $attendance->attend_end = Carbon::now()->locale('ja');
        $attendance->save();

        return view('general.index_end', ['currentDate' => $currentDate, 'currentTime' => $currentTime]);
    }

    public function rest_start()
    {
        $currentDate = Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日(ddd)');
        $currentTime = Carbon::now()->locale('ja')->isoFormat('HH:mm');
        $attendance = Attendance::where('user_id', auth()->id())->whereDate('attend_start', Carbon::today())->first();

        Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => Carbon::now()->locale('ja'),
        ]);

        return view('general.index_rest', ['currentDate' => $currentDate, 'currentTime' => $currentTime]);
    }

    public function rest_end()
    {
        $currentDate = Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日(ddd)');
        $currentTime = Carbon::now()->locale('ja')->isoFormat('HH:mm');
        $attendance = Attendance::where('user_id', auth()->id())->whereDate('attend_start', Carbon::today())->first();

        $rest = Rest::where('attendance_id', $attendance->id)->whereNull('rest_end')->first();
        $rest->rest_end = Carbon::now()->locale('ja');
        $rest->save();

        return view('general.index_attend', ['currentDate' => $currentDate, 'currentTime' => $currentTime]);
    }

    public function attendance_list()
    {
        $displayMonth = session('attendance_list_month', Carbon::now()->format('Y/m'));
        $attendances = [];
        $totalRests = [];
        $totalWorks = [];
        $previousMonth = Carbon::now()->subMonth()->format('Y/m');
        $nextMonth = Carbon::now()->addMonth()->format('Y/m');
        $latestModified = [];

        return view('general.index_list',
            ['attendances' => $attendances,
            'displayMonth' => $displayMonth,
            'totalRests' => $totalRests,
            'totalWorks' => $totalWorks,
            'previousMonth' => $previousMonth,
            'nextMonth' => $nextMonth,
            'latestModified' => $latestModified,
            ]);
    }

    public function attendance_list_admin()
    {
        $displayDay = session('attendance_list_day', Carbon::now()->format('Y/m/d'));
        $attendances = [];
        $totalRests = [];
        $totalWorks = [];
        $previousDay = Carbon::now()->subDay()->format('Y/m/d');
        $nextDay = Carbon::now()->addDay()->format('Y/m/d');
        $latestModified = [];

        return view('admin.index',
            ['attendances' => $attendances,
            'displayDay' => $displayDay,
            'totalRests' => $totalRests,
            'totalWorks' => $totalWorks,
            'previousDay' => $previousDay,
            'nextDay' => $nextDay,
            'latestModified' => $latestModified
            ]);
    }

    public function attendance_staff($id)
    {
        $displayMonth = session('attendance_list_month', Carbon::now()->format('Y/m'));
        $attendances = [];
        $totalRests = [];
        $totalWorks = [];
        $previousMonth = Carbon::now()->subMonth()->format('Y/m');
        $nextMonth = Carbon::now()->addMonth()->format('Y/m');
        $latestModified = [];
        $staff = User::findOrFail($id);

        return view('admin.index_staff_attendance',
            ['attendances' => $attendances,
            'displayMonth' => $displayMonth,
            'totalRests' => $totalRests,
            'totalWorks' => $totalWorks,
            'previousMonth' => $previousMonth,
            'nextMonth' => $nextMonth,
            'latestModified' => $latestModified,
            'staff' => $staff,
            ]);
    }

    public function attendance_detail($id)
    {
        $attendance = Attendance::with('user')->with('rests')->findOrFail($id);
        $modifiedAttendance = ModifiedAttendance::with('modifiedRests')->with('attendance.user')->with('administrator')->where('attendance_id', $id)->latest()->first();

        if ($modifiedAttendance && !$modifiedAttendance->administrator_id) {
            return view('general.index_detail_confirm', ['attendance' => $attendance, 'modifiedAttendance' => $modifiedAttendance]);
        }
        return view('general.index_detail', ['attendance' => $attendance, 'modifiedAttendance' => $modifiedAttendance]);
    }

    public function attendance_detail_admin($id)
    {
        $attendance = Attendance::with('user')->with('rests')->findOrFail($id);
        $modifiedAttendance = ModifiedAttendance::with('modifiedRests')->with('attendance.user')->with('administrator')->where('attendance_id', $id)->latest()->first();

        if ($modifiedAttendance) {
            return view('admin.index_detail_approve', ['attendance' => $attendance, 'modifiedAttendance' => $modifiedAttendance]);
        }
        return view('admin.index_detail', ['attendance' => $attendance, 'modifiedAttendance' => $modifiedAttendance]);
    }

    public function attendance_detail_add($day)
    {
        return view('general.index_detail_add', ['day' => $day]);
    }

    public function attendance_add(AttendanceRequest $request, $day)
    {
        $attendance = Attendance::create([
            'user_id' => auth()->id(),
            'attend_start' => Carbon::parse($day . ' ' . $request->input('attend_start'))->locale('ja'),
            'attend_end' => Carbon::parse($day . ' ' . $request->input('attend_end'))->locale('ja'),
        ]);

        Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => Carbon::parse($day . ' ' . $request->input('rest_start'))->locale('ja'),
            'rest_end' => Carbon::parse($day . ' ' . $request->input('rest_end'))->locale('ja'),
        ]);

        $modifiedAttendance = ModifiedAttendance::create([
            'attendance_id' => $attendance->id,
            'attend_start' => Carbon::parse($day . ' ' . $request->input('attend_start'))->locale('ja'),
            'attend_end' => Carbon::parse($day . ' ' . $request->input('attend_end'))->locale('ja'),
            'comment' => $request->input('comment'),
        ]);

        ModifiedRest::create([
                'modified_attendance_id' => $modifiedAttendance->id,
                'rest_start' => Carbon::parse($day . ' ' . $request->input('rest_start'))->locale('ja'),
                'rest_end' => Carbon::parse($day . ' ' . $request->input('rest_end'))->locale('ja'),
            ]);

        return redirect()->route('attendance_detail', ['id' => $attendance->id]);
    }

    public function attendance_detail_add_admin($id, $day)
    {
        $staff = User::findOrFail($id);
        return view('admin.index_detail_add', ['id' => $id, 'day' => $day, 'staff' => $staff]);
    }

    public function attendance_add_admin(AttendanceRequest $request, $id, $day)
    {
        $attendance = Attendance::create([
            'user_id' => $id,
            'attend_start' => Carbon::parse($day . ' ' . $request->input('attend_start'))->locale('ja'),
            'attend_end' => Carbon::parse($day . ' ' . $request->input('attend_end'))->locale('ja'),
        ]);

        Rest::create([
            'attendance_id' => $attendance->id,
            'rest_start' => Carbon::parse($day . ' ' . $request->input('rest_start'))->locale('ja'),
            'rest_end' => Carbon::parse($day . ' ' . $request->input('rest_end'))->locale('ja'),
        ]);

        $modifiedAttendance = ModifiedAttendance::create([
            'attendance_id' => $attendance->id,
            'attend_start' => Carbon::parse($day . ' ' . $request->input('attend_start'))->locale('ja'),
            'attend_end' => Carbon::parse($day . ' ' . $request->input('attend_end'))->locale('ja'),
            'comment' => $request->input('comment'),
        ]);

        ModifiedRest::create([
                'modified_attendance_id' => $modifiedAttendance->id,
                'rest_start' => Carbon::parse($day . ' ' . $request->input('rest_start'))->locale('ja'),
                'rest_end' => Carbon::parse($day . ' ' . $request->input('rest_end'))->locale('ja'),
            ]);

        return redirect()->route('attendance_detail_admin', ['id' => $attendance->id]);
    }

    public function attendance_modify(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendanceDate = $attendance->attend_start->toDateString();

        $modifiedAttendance = ModifiedAttendance::create([
            'attendance_id' => $id,
            'attend_start' => Carbon::parse($attendanceDate . ' ' . $request->input('attend_start'))->locale('ja'),
            'attend_end' => Carbon::parse($attendanceDate . ' ' . $request->input('attend_end'))->locale('ja'),
            'comment' => $request->input('comment'),
        ]);

        $rests = $request->input('rests', []);
        foreach ($rests as $index => $rest) {
            if (empty($rest['rest_start']) || empty($rest['rest_end'])) {
                continue;
            }

            ModifiedRest::create([
                'modified_attendance_id' => $modifiedAttendance->id,
                'rest_start' => Carbon::parse($attendanceDate . ' ' . $rest['rest_start'])->locale('ja'),
                'rest_end' => Carbon::parse($attendanceDate . ' ' . $rest['rest_end'])->locale('ja'),
            ]);
        }

        return redirect()->route('attendance_detail', ['id' => $id]);
    }

    public function attendance_modify_admin(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendanceDate = $attendance->attend_start->toDateString();

        $modifiedAttendance = ModifiedAttendance::create([
            'attendance_id' => $id,
            'attend_start' => Carbon::parse($attendanceDate . ' ' . $request->input('attend_start'))->locale('ja'),
            'attend_end' => Carbon::parse($attendanceDate . ' ' . $request->input('attend_end'))->locale('ja'),
            'comment' => $request->input('comment'),
        ]);

        $rests = $request->input('rests', []);
        foreach ($rests as $index => $rest) {
            if (empty($rest['rest_start']) || empty($rest['rest_end'])) {
                continue;
            }

            ModifiedRest::create([
                'modified_attendance_id' => $modifiedAttendance->id,
                'rest_start' => Carbon::parse($attendanceDate . ' ' . $rest['rest_start'])->locale('ja'),
                'rest_end' => Carbon::parse($attendanceDate . ' ' . $rest['rest_end'])->locale('ja'),
            ]);
        }

        return redirect()->route('attendance_detail_admin', ['id' => $id]);
    }

    public function attendance_request()
    {
        $attendanceRequests = ModifiedAttendance::with('attendance.user')
            ->whereHas('attendance', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->get();

        return view('general.index_request', ['attendanceRequests' => $attendanceRequests]);
    }

    public function attendance_request_admin()
    {
        $attendanceRequests = ModifiedAttendance::with('attendance.user')->get();

        return view('admin.index_request', ['attendanceRequests' => $attendanceRequests]);
    }

    public function attendance_approve($id)
    {
        $modifiedAttendance = ModifiedAttendance::findOrFail($id);
        $modifiedAttendance->administrator_id = auth()->guard('admin')->id();
        $modifiedAttendance->save();

        return redirect()->route('attendance_detail_admin', ['id' => $modifiedAttendance->attendance_id]);
    }

    public function staff_list()
    {
        $staffs = User::all();

        return view('admin.index_staff', ['staffs' => $staffs]);
    }

    public function attendance_export(Request $request, $id, $month)
    {
        $staff = User::findOrFail($id);
        $targetMonth = Carbon::parse($month . '-1');
        $attendances = Attendance::with(['user', 'rests', 'modifiedAttendances.modifiedRests'])
            ->where('user_id', $id)
            ->whereYear('attend_start', $targetMonth->year)
            ->whereMonth('attend_start', $targetMonth->month)
            ->orderBy('attend_start', 'asc')
            ->get();

        $exportRows = $attendances->map(function ($attendance) {
            $latestModified = $attendance->modifiedAttendances->last();
            $restSource = $latestModified && $latestModified->modifiedRests->isNotEmpty()
                ? $latestModified->modifiedRests
                : $attendance->rests;

            $totalRestMinutes = 0;
            foreach ($restSource as $rest) {
                if ($rest->rest_end) {
                    $totalRestMinutes += Carbon::parse($rest->rest_end)
                        ->diffInMinutes(Carbon::parse($rest->rest_start));
                }
            }

            $attendStart = $latestModified
                ? Carbon::parse($latestModified->attend_start)
                : ($attendance->attend_start ? Carbon::parse($attendance->attend_start) : null);

            $attendEnd = $latestModified
                ? ($latestModified->attend_end ? Carbon::parse($latestModified->attend_end) : null)
                : ($attendance->attend_end ? Carbon::parse($attendance->attend_end) : null);

            $totalWorkMinutes = 0;
            if ($attendStart && $attendEnd) {
                $totalWorkMinutes = $attendEnd->diffInMinutes($attendStart) - $totalRestMinutes;
            }

            $approvalStatus = '申請なし';
            if ($latestModified) {
                $approvalStatus = $latestModified->administrator_id ? '承認済み' : '承認待ち';
            }

            return [
                'staff_name' => $attendance->user->name ?? '',
                'date' => $attendStart ? $attendStart->copy()->locale('ja')->isoFormat('MM/DD(ddd)') : '',
                'attend_start' => $attendStart ? $attendStart->format('H:i') : '',
                'attend_end' => $attendEnd ? $attendEnd->format('H:i') : '',
                'total_rest' => sprintf('%d:%02d', intdiv($totalRestMinutes, 60), $totalRestMinutes % 60),
                'total_work' => sprintf('%d:%02d', intdiv($totalWorkMinutes, 60), $totalWorkMinutes % 60),
                'approval_status' => $approvalStatus,
            ];
        });

        $csvExporter = new Export();
        $csvExporter->build($exportRows, [
            'staff_name' => 'スタッフ名',
            'date' => '日付',
            'attend_start' => '出勤',
            'attend_end' => '退勤',
            'total_rest' => '休憩',
            'total_work' => '合計',
            'approval_status' => '承認ステータス',
        ]);

        $csvReader = $csvExporter->getReader();
        $csvReader->setOutputBOM(\League\Csv\Reader::BOM_UTF8);
        $csvReader->output($staff->name . "_" . $month . "月分_" . Carbon::now()->format('Ymd_His') . ".csv");
    }

}
