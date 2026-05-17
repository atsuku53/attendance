<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Administrator;
use App\Models\Attendance;
use App\Models\ModifiedAttendance;
use App\Models\Rest;
use Carbon\Carbon;
use Livewire\Livewire;
use App\Http\Livewire\SelectMonth;
use App\Http\Livewire\SelectMonthAdmin;
use App\Http\Livewire\SwitchApproval;
use App\Http\Livewire\SwitchApprovalAdmin;
use App\Http\Livewire\SelectDay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    //時計表示機能
    public function test_attendance_screen_shows_current_date()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $expectedDate = \Carbon\Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日(ddd)');
        $response->assertSee($expectedDate);
    }

    //勤務外ステータスの表示確認
    public function test_attendance_status_shows_off_work()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('勤務外');
    }

    //勤務中ステータスの表示確認
    public function test_attendance_status_shows_on_work()
    {
        $user = User::factory()->create();
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now();
        $attendance->save();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    //休憩中ステータスの表示確認
    public function test_attendance_status_shows_on_rest()
    {
        $user = User::factory()->create();
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->save();
        $rest = new Rest();
        $rest->attendance_id = $attendance->id;
        $rest->rest_start = now()->subHour();
        $rest->save();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    //退勤済みステータスの表示確認
    public function test_attendance_status_shows_off_work_after_clock_out()
    {
        $user = User::factory()->create();
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }

    //出勤ボタンの動作確認
    public function test_attendance_input_attend()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('出勤');
        $response->assertSee('<a href="' . route('attend_start') . '" class="attendance__button--display">出勤</a>', false);

        $response = $this->get(route('attend_start'));
        $response->assertSee('出勤中');
    }

    //出勤は1日に1回しかできないことの確認
    public function test_attendance_input_attend_once_per_day()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now();
        $attendance->save();

        $response = $this->get(route('attend_start'));
        $response->assertDontSee('<a href="' . route('attend_start') . '" class="attendance__button--display">出勤</a>', false);
    }

    //勤怠一覧での出勤時間の表示確認
    public function test_attendance_list_shows_attend_start_time()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->save();

        $response = $this->get('/attendance/list');
        $response->assertSee($attendance->attend_start->locale('ja')->isoFormat('MM/DD(ddd)'));
        $response->assertSee($attendance->attend_start->locale('ja')->isoFormat('HH:mm'));
    }

    //休憩ボタンの動作確認
    public function test_attendance_input_rest()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->save();

        $response = $this->get('/attendance');
        $response->assertSee('<a href="' . route('rest_start') . '" class="attendance-rest__button--display">休憩入</a>', false);

        $response = $this->get(route('rest_start'));
        $response->assertSee('休憩中');
    }

    //休憩は1日に複数回できることの確認
    public function test_attendance_input_rest_multiple_times_per_day()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->save();

        $response = $this->get(route('rest_start'));
        $response = $this->get(route('rest_end'));
        $response->assertSee('<a href="' . route('rest_start') . '" class="attendance-rest__button--display">休憩入</a>', false);
    }

    //休憩戻ボタンの動作確認
    public function test_attendance_input_rest_end()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->save();

        $response = $this->get('/attendance');
        $response = $this->get(route('rest_start'));
        $response->assertSee('<a href="' . route('rest_end') . '" class="attendance-rest__button--display">休憩戻</a>', false);

        $response = $this->get(route('rest_end'));
        $response->assertSee('出勤中');
    }

    //勤怠一覧での休憩時間の表示確認
    public function test_attendance_list_shows_rest_time()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->save();
        $rest = new Rest();
        $rest->attendance_id = $attendance->id;
        $rest->rest_start = now()->subHour();
        $rest->rest_end = now();
        $rest->save();
        $totalRestMinutes = $rest->rest_start->diffInMinutes($rest->rest_end);
        $totalRest = sprintf('%d:%02d', intdiv($totalRestMinutes, 60), $totalRestMinutes % 60);

        $response = $this->get('/attendance/list');
        $response->assertSee($totalRest);
    }

    //退勤ボタンの動作確認
    public function test_attendance_input_attend_end()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->save();

        $response = $this->get('/attendance');
        $response->assertSee('<a href="' . route('attend_end') . '" class="attendance__button--display">退勤</a>', false);

        $response = $this->get(route('attend_end'));
        $response->assertSee('退勤済');
    }

    //勤怠一覧での退勤時間の表示確認
    public function test_attendance_list_shows_attend_end_time()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $response = $this->get('/attendance/list');
        $response->assertSee($attendance->attend_end->locale('ja')->isoFormat('HH:mm'));
    }

    //勤怠一覧での勤怠情報の表示確認
    public function test_attendance_list_shows_attendance_info()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();
        $rest = new Rest();
        $rest->attendance_id = $attendance->id;
        $rest->rest_start = now()->subHour();
        $rest->rest_end = now();
        $rest->save();
        $totalAttendanceMinutes = $attendance->attend_start->diffInMinutes($attendance->attend_end);
        $totalRestMinutes = $rest->rest_start->diffInMinutes($rest->rest_end);
        $totalWorkMinutes = $totalAttendanceMinutes - $totalRestMinutes;
        $totalRest = sprintf('%d:%02d', intdiv($totalRestMinutes, 60), $totalRestMinutes % 60);
        $totalWork = sprintf('%d:%02d', intdiv($totalWorkMinutes, 60), $totalWorkMinutes % 60);

        $response = $this->get('/attendance/list');
        $response->assertSee($attendance->attend_start->locale('ja')->isoFormat('MM/DD(ddd)'));
        $response->assertSee($attendance->attend_start->locale('ja')->isoFormat('HH:mm'));
        $response->assertSee($attendance->attend_end->locale('ja')->isoFormat('HH:mm'));
        $response->assertSee($totalRest);
        $response->assertSee($totalWork);
    }

    //勤怠一覧での現在の月の表示確認
    public function test_attendance_list_shows_current_month()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance/list');
        $expectedMonth = Carbon::now()->locale('ja')->isoFormat('YYYY/MM');
        $response->assertSee($expectedMonth);
    }

    //勤怠一覧での前月の表示確認
    public function test_attendance_list_shows_previous_month()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $currentMonth = Carbon::now()->locale('ja');
        $previousMonth = Carbon::now()->subMonth()->locale('ja');

        Livewire::test(SelectMonth::class, [
            'displayMonth' => $currentMonth->isoFormat('YYYY/MM'),
        ])
            ->assertSeeHtml('wire:click="goToPreviousMonth"')
            ->assertSet('selectedMonth', $currentMonth->format('Y-m'))
            ->call('goToPreviousMonth')
            ->assertSet('displayMonth', $previousMonth->isoFormat('YYYY/MM'));
    }

    //勤怠一覧での翌月の表示確認
    public function test_attendance_list_shows_next_month()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $currentMonth = Carbon::now()->locale('ja');
        $nextMonth = Carbon::now()->addMonth()->locale('ja');

        Livewire::test(SelectMonth::class, [
            'displayMonth' => $currentMonth->isoFormat('YYYY/MM'),
        ])
            ->assertSeeHtml('wire:click="goToNextMonth"')
            ->assertSet('selectedMonth', $currentMonth->format('Y-m'))
            ->call('goToNextMonth')
            ->assertSet('displayMonth', $nextMonth->isoFormat('YYYY/MM'));
    }

    //勤怠一覧での詳細ボタンの動作確認
    public function test_attendance_list_detail_button()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $response = $this->get('/attendance/list');
        $response = $this->get(route('attendance_detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
    }

    //勤怠詳細での名前がログインユーザーの名前であることの確認
    public function test_attendance_detail_shows_user_name()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $response = $this->get(route('attendance_detail', ['id' => $attendance->id]));
        $response->assertSee($user->name);
    }

    //勤怠詳細での日付の表示確認
    public function test_attendance_detail_shows_date()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $response = $this->get(route('attendance_detail', ['id' => $attendance->id]));
        $expectedYear = $attendance->attend_start->locale('ja')->isoFormat('YYYY年');
        $expectedDate = $attendance->attend_start->locale('ja')->isoFormat('M月D日');
        $response->assertSee($expectedYear);
        $response->assertSee($expectedDate);
    }

    //勤怠詳細での出勤・退勤時間の表示確認
    public function test_attendance_detail_shows_attend_times()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $response = $this->get(route('attendance_detail', ['id' => $attendance->id]));
        $response->assertSee($attendance->attend_start->locale('ja')->isoFormat('HH:mm'));
        $response->assertSee($attendance->attend_end->locale('ja')->isoFormat('HH:mm'));
    }

    //勤怠詳細での休憩時間の表示確認
    public function test_attendance_detail_shows_rest_times()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();
        $rest = new Rest();
        $rest->attendance_id = $attendance->id;
        $rest->rest_start = now()->subHour();
        $rest->rest_end = now();
        $rest->save();

        $response = $this->get(route('attendance_detail', ['id' => $attendance->id]));
        $response->assertSee($rest->rest_start->locale('ja')->isoFormat('HH:mm'));
        $response->assertSee($rest->rest_end->locale('ja')->isoFormat('HH:mm'));
    }

    //勤怠詳細にて出勤時間が退勤時間よりも後になっている場合のバリデーション
    public function test_attendance_detail_modify_attend_times_validation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $response = $this->post(route('attendance_modify', ['id' => $attendance->id]), [
            'attend_start' => now()->format('H:i'),
            'attend_end' => now()->subHours(2)->format('H:i'),
            'comment' => 'テストコメント',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('attend_start');
        $errors = session('errors');
        $this->assertEquals('出勤時間が不適切な値です', $errors->first('attend_start'));
    }

    //勤怠詳細にて休憩開始時間が退勤時間よりも後になっている場合のバリデーション
    public function test_attendance_detail_modify_rest_start_time_validation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $response = $this->post(route('attendance_modify', ['id' => $attendance->id]), [
            'attend_start' => now()->subHours(2)->format('H:i'),
            'attend_end' => now()->format('H:i'),
            'rest_start' => now()->addHour()->format('H:i'),
            'comment' => 'テストコメント',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('rest_start');
        $errors = session('errors');
        $this->assertEquals('休憩時間が不適切な値です', $errors->first('rest_start'));
    }

    //勤怠詳細にて休憩終了時間が退勤時間よりも後になっている場合のバリデーション
    public function test_attendance_detail_modify_rest_end_time_validation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $response = $this->post(route('attendance_modify', ['id' => $attendance->id]), [
            'attend_start' => now()->subHours(2)->format('H:i'),
            'attend_end' => now()->format('H:i'),
            'rest_start' => now()->subHour()->format('H:i'),
            'rest_end' => now()->addHour()->format('H:i'),
            'comment' => 'テストコメント',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('rest_end');
        $errors = session('errors');
        $this->assertEquals('休憩時間もしくは退勤時間が不適切な値です', $errors->first('rest_end'));
    }

    //勤怠詳細にて備考が未入力の場合のバリデーション
    public function test_attendance_detail_modify_comment_validation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $response = $this->post(route('attendance_modify', ['id' => $attendance->id]), [
            'attend_start' => now()->subHours(2)->format('H:i'),
            'attend_end' => now()->format('H:i'),
            'comment' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('comment');
        $errors = session('errors');
        $this->assertEquals('備考を記入してください', $errors->first('comment'));
    }

    //勤怠詳細にて修正申請が行われることの確認
    public function test_attendance_detail_modify_creates_correction_request()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $response = $this->post(route('attendance_modify', ['id' => $attendance->id]), [
            'attend_start' => now()->subHours(1)->format('H:i'),
            'attend_end' => now()->format('H:i'),
            'comment' => '修正申請のテスト',
        ]);

        $response->assertStatus(302);
        $response = $this->get(route('attendance_detail', ['id' => $attendance->id]));
        $response->assertSee('*承認待ちのため修正はできません。');

        $modifiedAttendance = ModifiedAttendance::where('attendance_id', $attendance->id)->first();
        $attendanceRequests = ModifiedAttendance::with('attendance.user')->get();

        Livewire::test(SwitchApprovalAdmin::class, [
            'activeTab' => 'tab1',
            'attendanceRequests' => $attendanceRequests,
            ])
            ->assertSee('承認待ち')
            ->assertSee($user->name)
            ->assertSee($attendance->attend_start->locale('ja')->isoFormat('YYYY/MM/DD'))
            ->assertSee('修正申請のテスト')
            ->assertSee($modifiedAttendance->created_at->locale('ja')->isoFormat('YYYY/MM/DD'));
    }

    //申請一覧の「承認待ち」に修正申請が表示されることの確認
    public function test_correction_request_appears_in_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $response = $this->post(route('attendance_modify', ['id' => $attendance->id]), [
            'attend_start' => now()->subHours(1)->format('H:i'),
            'attend_end' => now()->format('H:i'),
            'comment' => '修正申請のテスト',
        ]);

        $response->assertStatus(302);

        $modifiedAttendance = ModifiedAttendance::where('attendance_id', $attendance->id)->first();
        $attendanceRequests = ModifiedAttendance::with('attendance.user')->get();

        Livewire::test(SwitchApproval::class, [
            'activeTab' => 'tab1',
            'attendanceRequests' => $attendanceRequests,
            ])
            ->assertSee('承認待ち')
            ->assertSee($user->name)
            ->assertSee($attendance->attend_start->locale('ja')->isoFormat('YYYY/MM/DD'))
            ->assertSee('修正申請のテスト')
            ->assertSee($modifiedAttendance->created_at->locale('ja')->isoFormat('YYYY/MM/DD'));
    }

    //申請一覧の「承認済み」に管理者が承認した修正申請が表示されることの確認
    public function test_approved_correction_request_appears_in_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $response = $this->post(route('attendance_modify', ['id' => $attendance->id]), [
            'attend_start' => now()->subHours(1)->format('H:i'),
            'attend_end' => now()->format('H:i'),
            'comment' => '修正申請のテスト',
        ]);

        $response->assertStatus(302);

        $administrator = new Administrator();
        $administrator->name = 'Admin';
        $administrator->email = 'admin@example.com';
        $administrator->password = bcrypt('password');
        $administrator->save();

        $modifiedAttendance = ModifiedAttendance::where('attendance_id', $attendance->id)->first();
        $modifiedAttendance->administrator_id = $administrator->id;
        $modifiedAttendance->save();
        $attendanceRequests = ModifiedAttendance::with('attendance.user')->get();

        Livewire::test(SwitchApproval::class, [
            'activeTab' => 'tab1',
            'attendanceRequests' => $attendanceRequests,
            ])
            ->call('switchTab', 'tab2')
            ->assertSee('承認済み')
            ->assertSee($user->name)
            ->assertSee($attendance->attend_start->locale('ja')->isoFormat('YYYY/MM/DD'))
            ->assertSee('修正申請のテスト')
            ->assertSee($modifiedAttendance->created_at->locale('ja')->isoFormat('YYYY/MM/DD'));
    }

    //申請一覧の各申請の「詳細」を押下すると勤怠詳細画面に遷移することの確認
    public function test_correction_request_detail_button()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $response = $this->post(route('attendance_modify', ['id' => $attendance->id]), [
            'attend_start' => now()->subHours(1)->format('H:i'),
            'attend_end' => now()->format('H:i'),
            'comment' => '修正申請のテスト',
        ]);

        $response->assertStatus(302);

        $modifiedAttendance = ModifiedAttendance::where('attendance_id', $attendance->id)->first();
        $attendanceRequests = ModifiedAttendance::with('attendance.user')->get();

        Livewire::test(SwitchApproval::class, [
            'activeTab' => 'tab1',
            'attendanceRequests' => $attendanceRequests,
            ])
            ->assertSee('承認待ち')
            ->assertSee($user->name)
            ->assertSee($attendance->attend_start->locale('ja')->isoFormat('YYYY/MM/DD'))
            ->assertSee('修正申請のテスト')
            ->assertSee($modifiedAttendance->created_at->locale('ja')->isoFormat('YYYY/MM/DD'))
            ->assertSeeHtml('<a href="/attendance/detail/' . $modifiedAttendance->attendance_id . '" class="approval-detail__link">詳細</a>', false);

        $response = $this->get('/attendance/detail/' . $modifiedAttendance->attendance_id);
        $response->assertStatus(200);
    }

    //管理者の勤怠一覧でその日の全ユーザーの勤怠情報が表示されることの確認
    public function test_admin_attendance_list_shows_user_attendance()
    {
        $users = User::factory()->count(3)->create();
        $administrator = new Administrator();
        $administrator->name = 'Admin';
        $administrator->email = 'admin@example.com';
        $administrator->password = bcrypt('password');
        $administrator->save();

        foreach ($users as $user) {
            $attendance = new Attendance();
            $attendance->user_id = $user->id;
            $attendance->attend_start = now()->subHours(3);
            $attendance->attend_end = now();
            $attendance->save();
            $rest = new Rest();
            $rest->attendance_id = $attendance->id;
            $rest->rest_start = now()->subHours(2);
            $rest->rest_end = now()->subHour();
            $rest->save();
            $totalAttendanceMinutes = $attendance->attend_start->diffInMinutes($attendance->attend_end);
            $totalRestMinutes = $rest->rest_start->diffInMinutes($rest->rest_end);
            $totalWorkMinutes = $totalAttendanceMinutes - $totalRestMinutes;
            $totalRest = sprintf('%d:%02d', intdiv($totalRestMinutes, 60), $totalRestMinutes % 60);
            $totalWork = sprintf('%d:%02d', intdiv($totalWorkMinutes, 60), $totalWorkMinutes % 60);

            $response = $this->actingAs($administrator, 'admin')->get('/admin/attendance/list');
            $response->assertSee($user->name);
            $response->assertSee($attendance->attend_start->locale('ja')->isoFormat('HH:mm'));
            $response->assertSee($attendance->attend_end->locale('ja')->isoFormat('HH:mm'));
            $response->assertSee($totalRest);
            $response->assertSee($totalWork);
        }
    }

    //管理者の勤怠一覧でその日の日付が表示されることの確認
    public function test_admin_attendance_list_shows_current_date()
    {
        $administrator = new Administrator();
        $administrator->name = 'Admin';
        $administrator->email = 'admin@example.com';
        $administrator->password = bcrypt('password');
        $administrator->save();
        $response = $this->actingAs($administrator, 'admin')->get('/admin/attendance/list');
        $expectedDate = Carbon::now()->locale('ja')->isoFormat('YYYY/MM/DD');
        $response->assertSee($expectedDate);
    }

    //管理者の勤怠一覧での前日の勤怠情報の表示確認
    public function test_admin_attendance_list_shows_previous_day_attendance()
    {
        $user = User::factory()->create();
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subDay()->subHours(3);
        $attendance->attend_end = now()->subDay();
        $attendance->save();
        $rest = new Rest();
        $rest->attendance_id = $attendance->id;
        $rest->rest_start = now()->subDay()->subHours(2);
        $rest->rest_end = now()->subDay()->subHour();
        $rest->save();
        $totalAttendanceMinutes = $attendance->attend_start->diffInMinutes($attendance->attend_end);
        $totalRestMinutes = $rest->rest_start->diffInMinutes($rest->rest_end);
        $totalWorkMinutes = $totalAttendanceMinutes - $totalRestMinutes;
        $totalRest = sprintf('%d:%02d', intdiv($totalRestMinutes, 60), $totalRestMinutes % 60);
        $totalWork = sprintf('%d:%02d', intdiv($totalWorkMinutes, 60), $totalWorkMinutes % 60);

        $currentDay = Carbon::now()->locale('ja');
        $previousDay = Carbon::now()->subDay()->locale('ja');

        Livewire::test(SelectDay::class, [
            'displayDay' => $currentDay->isoFormat('YYYY/MM/DD'),
        ])
            ->assertSeeHtml('wire:click="goToPreviousDay"')
            ->assertSet('selectedDay', $currentDay->format('Y-m-d'))
            ->call('goToPreviousDay')
            ->assertSet('displayDay', $previousDay->isoFormat('YYYY/MM/DD'))
            ->assertSee($user->name)
            ->assertSee($attendance->attend_start->locale('ja')->isoFormat('HH:mm'))
            ->assertSee($attendance->attend_end->locale('ja')->isoFormat('HH:mm'))
            ->assertSee($totalRest)
            ->assertSee($totalWork);
    }

    //管理者の勤怠一覧での翌日の勤怠情報の表示確認
    public function test_admin_attendance_list_shows_next_day_attendance()
    {
        $user = User::factory()->create();
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->addDay()->subHours(3);
        $attendance->attend_end = now()->addDay();
        $attendance->save();
        $rest = new Rest();
        $rest->attendance_id = $attendance->id;
        $rest->rest_start = now()->addDay()->subHours(2);
        $rest->rest_end = now()->addDay()->subHour();
        $rest->save();
        $totalAttendanceMinutes = $attendance->attend_start->diffInMinutes($attendance->attend_end);
        $totalRestMinutes = $rest->rest_start->diffInMinutes($rest->rest_end);
        $totalWorkMinutes = $totalAttendanceMinutes - $totalRestMinutes;
        $totalRest = sprintf('%d:%02d', intdiv($totalRestMinutes, 60), $totalRestMinutes % 60);
        $totalWork = sprintf('%d:%02d', intdiv($totalWorkMinutes, 60), $totalWorkMinutes % 60);

        $currentDay = Carbon::now()->locale('ja');
        $nextDay = Carbon::now()->addDay()->locale('ja');

        Livewire::test(SelectDay::class, [
            'displayDay' => $currentDay->isoFormat('YYYY/MM/DD'),
        ])
            ->assertSeeHtml('wire:click="goToNextDay"')
            ->assertSet('selectedDay', $currentDay->format('Y-m-d'))
            ->call('goToNextDay')
            ->assertSet('displayDay', $nextDay->isoFormat('YYYY/MM/DD'))
            ->assertSee($user->name)
            ->assertSee($attendance->attend_start->locale('ja')->isoFormat('HH:mm'))
            ->assertSee($attendance->attend_end->locale('ja')->isoFormat('HH:mm'))
            ->assertSee($totalRest)
            ->assertSee($totalWork);
    }

    //管理者の勤怠詳細の表示確認
    public function test_admin_attendance_detail_shows_attendance_info()
    {
        $user = User::factory()->create();
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();
        $rest = new Rest();
        $rest->attendance_id = $attendance->id;
        $rest->rest_start = now()->subHour();
        $rest->rest_end = now();
        $rest->save();

        $administrator = new Administrator();
        $administrator->name = 'Admin';
        $administrator->email = 'admin@example.com';
        $administrator->password = bcrypt('password');
        $administrator->save();

        $response = $this->actingAs($administrator, 'admin')->get('/admin/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($attendance->attend_start->locale('ja')->isoFormat('YYYY年'));
        $response->assertSee($attendance->attend_start->locale('ja')->isoFormat('M月D日'));
        $response->assertSee($attendance->attend_start->locale('ja')->isoFormat('HH:mm'));
        $response->assertSee($attendance->attend_end->locale('ja')->isoFormat('HH:mm'));
        $response->assertSee($rest->rest_start->locale('ja')->isoFormat('HH:mm'));
        $response->assertSee($rest->rest_end->locale('ja')->isoFormat('HH:mm'));
    }

    //管理者の勤怠詳細にて出勤時間が退勤時間よりも後になっている場合のバリデーション
    public function test_admin_attendance_detail_modify_attend_times_validation()
    {
        $user = User::factory()->create();
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $administrator = new Administrator();
        $administrator->name = 'Admin';
        $administrator->email = 'admin@example.com';
        $administrator->password = bcrypt('password');
        $administrator->save();

        $response = $this->actingAs($administrator, 'admin')->post('/admin/attendance/detail/' . $attendance->id, [
            'attend_start' => now()->format('H:i'),
            'attend_end' => now()->subHours(2)->format('H:i'),
            'comment' => '管理者修正のテスト',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('attend_start');
        $errors = session('errors');
        $this->assertEquals('出勤時間が不適切な値です', $errors->first('attend_start'));
    }

    //管理者の勤怠詳細にて休憩開始時間が退勤時間よりも後になっている場合のバリデーション
    public function test_admin_attendance_detail_modify_rest_start_time_validation()
    {
        $user = User::factory()->create();
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $administrator = new Administrator();
        $administrator->name = 'Admin';
        $administrator->email = 'admin@example.com';
        $administrator->password = bcrypt('password');
        $administrator->save();

        $response = $this->actingAs($administrator, 'admin')->post('/admin/attendance/detail/' . $attendance->id, [
            'attend_start' => now()->subHours(2)->format('H:i'),
            'attend_end' => now()->format('H:i'),
            'rest_start' => now()->addHour()->format('H:i'),
            'comment' => '管理者修正のテスト',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('rest_start');
        $errors = session('errors');
        $this->assertEquals('休憩時間が不適切な値です', $errors->first('rest_start'));
    }

    //管理者の勤怠詳細にて休憩終了時間が退勤時間よりも後になっている場合のバリデーション
    public function test_admin_attendance_detail_modify_rest_end_time_validation()
    {
        $user = User::factory()->create();
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $administrator = new Administrator();
        $administrator->name = 'Admin';
        $administrator->email = 'admin@example.com';
        $administrator->password = bcrypt('password');
        $administrator->save();

        $response = $this->actingAs($administrator, 'admin')->post('/admin/attendance/detail/' . $attendance->id, [
            'attend_start' => now()->subHours(2)->format('H:i'),
            'attend_end' => now()->format('H:i'),
            'rest_start' => now()->subHour()->format('H:i'),
            'rest_end' => now()->addHour()->format('H:i'),
            'comment' => '管理者修正のテスト',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('rest_end');
        $errors = session('errors');
        $this->assertEquals('休憩時間もしくは退勤時間が不適切な値です', $errors->first('rest_end'));
    }

    //管理者の勤怠詳細にて備考が未入力の場合のバリデーション
    public function test_admin_attendance_detail_modify_comment_validation()
    {
        $user = User::factory()->create();
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $administrator = new Administrator();
        $administrator->name = 'Admin';
        $administrator->email = 'admin@example.com';
        $administrator->password = bcrypt('password');
        $administrator->save();

        $response = $this->actingAs($administrator, 'admin')->post('/admin/attendance/detail/' . $attendance->id, [
            'attend_start' => now()->subHours(2)->format('H:i'),
            'attend_end' => now()->format('H:i'),
            'comment' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('comment');
        $errors = session('errors');
        $this->assertEquals('備考を記入してください', $errors->first('comment'));
    }

    //管理者のスタッフ一覧画面でスタッフの名前とメールアドレスが表示されることの確認
    public function test_admin_staff_list_shows_user_info()
    {
        $users = User::factory()->count(3)->create();
        $administrator = new Administrator();
        $administrator->name = 'Admin';
        $administrator->email = 'admin@example.com';
        $administrator->password = bcrypt('password');
        $administrator->save();
        $response = $this->actingAs($administrator, 'admin')->get('/admin/staff/list');
        $response->assertStatus(200);
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    //管理者のスタッフ一覧画面から「詳細」を押下しスタッフの勤怠一覧が表示されることの確認
    public function test_admin_staff_list_detail_button()
    {
        $user = User::factory()->create();
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(3);
        $attendance->attend_end = now();
        $attendance->save();
        $rest = new Rest();
        $rest->attendance_id = $attendance->id;
        $rest->rest_start = now()->subHours(2);
        $rest->rest_end = now()->subHour();
        $rest->save();
        $totalAttendanceMinutes = $attendance->attend_start->diffInMinutes($attendance->attend_end);
        $totalRestMinutes = $rest->rest_start->diffInMinutes($rest->rest_end);
        $totalWorkMinutes = $totalAttendanceMinutes - $totalRestMinutes;
        $totalRest = sprintf('%d:%02d', intdiv($totalRestMinutes, 60), $totalRestMinutes % 60);
        $totalWork = sprintf('%d:%02d', intdiv($totalWorkMinutes, 60), $totalWorkMinutes % 60);

        $administrator = new Administrator();
        $administrator->name = 'Admin';
        $administrator->email = 'admin@example.com';
        $administrator->password = bcrypt('password');
        $administrator->save();

        $response = $this->actingAs($administrator, 'admin')->get('/admin/staff/list');
        $response->assertSee('<a href="/admin/attendance/staff/' . $user->id . '" class="staff-detail__link">詳細</a>', false);
        $response = $this->actingAs($administrator, 'admin')->get('/admin/attendance/staff/' . $user->id);
        $response->assertSee($user->name);
        $response->assertSee($attendance->attend_start->locale('ja')->isoFormat('YYYY/MM'));
        $response->assertSee($attendance->attend_start->locale('ja')->isoFormat('MM/DD(ddd)'));
        $response->assertSee($attendance->attend_end->locale('ja')->isoFormat('HH:mm'));
        $response->assertSee($attendance->attend_end->locale('ja')->isoFormat('HH:mm'));
        $response->assertSee($totalRest);
        $response->assertSee($totalWork);
    }

    //管理者のスタッフ勤怠一覧での前月の表示確認
    public function test_admin_staff_attendance_list_shows_previous_month()
    {
        $user = User::factory()->create();
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subMonth()->subHours(3);
        $attendance->attend_end = now()->subMonth();
        $attendance->save();
        $rest = new Rest();
        $rest->attendance_id = $attendance->id;
        $rest->rest_start = now()->subMonth()->subHours(2);
        $rest->rest_end = now()->subMonth()->subHour();
        $rest->save();
        $totalAttendanceMinutes = $attendance->attend_start->diffInMinutes($attendance->attend_end);
        $totalRestMinutes = $rest->rest_start->diffInMinutes($rest->rest_end);
        $totalWorkMinutes = $totalAttendanceMinutes - $totalRestMinutes;
        $totalRest = sprintf('%d:%02d', intdiv($totalRestMinutes, 60), $totalRestMinutes % 60);
        $totalWork = sprintf('%d:%02d', intdiv($totalWorkMinutes, 60), $totalWorkMinutes % 60);

        $currentMonth = Carbon::now()->locale('ja');
        $previousMonth = Carbon::now()->subMonth()->locale('ja');

        Livewire::test(SelectMonthAdmin::class, [
            'displayMonth' => $currentMonth->isoFormat('YYYY/MM'),
            'staff' => $user,
        ])
            ->assertSeeHtml('wire:click="goToPreviousMonth"')
            ->assertSet('selectedMonth', $currentMonth->format('Y-m'))
            ->call('goToPreviousMonth')
            ->assertSet('displayMonth', $previousMonth->isoFormat('YYYY/MM'))
            ->assertSee($attendance->attend_start->locale('ja')->isoFormat('MM/DD(ddd)'))
            ->assertSee($attendance->attend_start->locale('ja')->isoFormat('HH:mm'))
            ->assertSee($attendance->attend_end->locale('ja')->isoFormat('HH:mm'))
            ->assertSee($totalRest)
            ->assertSee($totalWork);
    }

    //管理者のスタッフ勤怠一覧での翌月の表示確認
    public function test_admin_staff_attendance_list_shows_next_month()
    {
        $user = User::factory()->create();
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->addMonth()->subHours(3);
        $attendance->attend_end = now()->addMonth();
        $attendance->save();
        $rest = new Rest();
        $rest->attendance_id = $attendance->id;
        $rest->rest_start = now()->addMonth()->subHours(2);
        $rest->rest_end = now()->addMonth()->subHour();
        $rest->save();
        $totalAttendanceMinutes = $attendance->attend_start->diffInMinutes($attendance->attend_end);
        $totalRestMinutes = $rest->rest_start->diffInMinutes($rest->rest_end);
        $totalWorkMinutes = $totalAttendanceMinutes - $totalRestMinutes;
        $totalRest = sprintf('%d:%02d', intdiv($totalRestMinutes, 60), $totalRestMinutes % 60);
        $totalWork = sprintf('%d:%02d', intdiv($totalWorkMinutes, 60), $totalWorkMinutes % 60);

        $currentMonth = Carbon::now()->locale('ja');
        $nextMonth = Carbon::now()->addMonth()->locale('ja');

        Livewire::test(SelectMonthAdmin::class, [
            'displayMonth' => $currentMonth->isoFormat('YYYY/MM'),
            'staff' => $user,
        ])
            ->assertSeeHtml('wire:click="goToNextMonth"')
            ->assertSet('selectedMonth', $currentMonth->format('Y-m'))
            ->call('goToNextMonth')
            ->assertSet('displayMonth', $nextMonth->isoFormat('YYYY/MM'))
            ->assertSee($attendance->attend_start->locale('ja')->isoFormat('MM/DD(ddd)'))
            ->assertSee($attendance->attend_start->locale('ja')->isoFormat('HH:mm'))
            ->assertSee($attendance->attend_end->locale('ja')->isoFormat('HH:mm'))
            ->assertSee($totalRest)
            ->assertSee($totalWork);
    }

    //管理者のスタッフ勤怠一覧での勤怠詳細の表示確認
    public function test_admin_staff_attendance_list_shows_attendance_detail()
    {
        $user = User::factory()->create();
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(3);
        $attendance->attend_end = now();
        $attendance->save();
        $rest = new Rest();
        $rest->attendance_id = $attendance->id;
        $rest->rest_start = now()->subHours(2);
        $rest->rest_end = now()->subHour();
        $rest->save();

        $administrator = new Administrator();
        $administrator->name = 'Admin';
        $administrator->email = 'admin@example.com';
        $administrator->password = bcrypt('password');
        $administrator->save();

        $response = $this->actingAs($administrator, 'admin')->get('/admin/attendance/staff/' . $user->id);
        $response->assertStatus(200);
        $response->assertSee('<a href="/admin/attendance/detail/' . $attendance->id . '" class="attendance-detail__link">詳細</a>', false);
        $response = $this->actingAs($administrator, 'admin')->get('/admin/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($attendance->attend_start->locale('ja')->isoFormat('YYYY年'));
        $response->assertSee($attendance->attend_start->locale('ja')->isoFormat('M月D日'));
        $response->assertSee($attendance->attend_start->locale('ja')->isoFormat('HH:mm'));
        $response->assertSee($attendance->attend_end->locale('ja')->isoFormat('HH:mm'));
        $response->assertSee($rest->rest_start->locale('ja')->isoFormat('HH:mm'));
        $response->assertSee($rest->rest_end->locale('ja')->isoFormat('HH:mm'));
    }

    //管理者の申請一覧の「承認待ち」に修正申請が表示されることの確認
    public function test_admin_correction_request_appears_in_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $response = $this->post(route('attendance_modify', ['id' => $attendance->id]), [
            'attend_start' => now()->subHours(1)->format('H:i'),
            'attend_end' => now()->format('H:i'),
            'comment' => '修正申請のテスト',
        ]);

        $response->assertStatus(302);

        $modifiedAttendance = ModifiedAttendance::where('attendance_id', $attendance->id)->first();
        $attendanceRequests = ModifiedAttendance::with('attendance.user')->get();

        Livewire::test(SwitchApprovalAdmin::class, [
            'activeTab' => 'tab1',
            'attendanceRequests' => $attendanceRequests,
            ])
            ->assertSee('承認待ち')
            ->assertSee($user->name)
            ->assertSee($attendance->attend_start->locale('ja')->isoFormat('YYYY/MM/DD'))
            ->assertSee('修正申請のテスト')
            ->assertSee($modifiedAttendance->created_at->locale('ja')->isoFormat('YYYY/MM/DD'));
    }

    //管理者の申請一覧の「承認済み」に管理者が承認した修正申請が表示されることの確認
    public function test_admin_approved_correction_request_appears_in_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $response = $this->post(route('attendance_modify', ['id' => $attendance->id]), [
            'attend_start' => now()->subHours(1)->format('H:i'),
            'attend_end' => now()->format('H:i'),
            'comment' => '修正申請のテスト',
        ]);

        $response->assertStatus(302);

        $administrator = new Administrator();
        $administrator->name = 'Admin';
        $administrator->email = 'admin@example.com';
        $administrator->password = bcrypt('password');
        $administrator->save();

        $modifiedAttendance = ModifiedAttendance::where('attendance_id', $attendance->id)->first();
        $modifiedAttendance->administrator_id = $administrator->id;
        $modifiedAttendance->save();
        $attendanceRequests = ModifiedAttendance::with('attendance.user')->get();

        Livewire::test(SwitchApprovalAdmin::class, [
            'activeTab' => 'tab1',
            'attendanceRequests' => $attendanceRequests,
            ])
            ->call('switchTab', 'tab2')
            ->assertSee('承認済み')
            ->assertSee($user->name)
            ->assertSee($attendance->attend_start->locale('ja')->isoFormat('YYYY/MM/DD'))
            ->assertSee('修正申請のテスト')
            ->assertSee($modifiedAttendance->created_at->locale('ja')->isoFormat('YYYY/MM/DD'));
    }

    //管理者の申請一覧の各申請の「詳細」を押下すると勤怠詳細画面に遷移することの確認
    public function test_admin_correction_request_detail_button()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $response = $this->post(route('attendance_modify', ['id' => $attendance->id]), [
            'attend_start' => now()->subHours(1)->format('H:i'),
            'attend_end' => now()->format('H:i'),
            'comment' => '修正申請のテスト',
        ]);

        $response->assertStatus(302);

        $modifiedAttendance = ModifiedAttendance::where('attendance_id', $attendance->id)->first();
        $attendanceRequests = ModifiedAttendance::with('attendance.user')->get();

        Livewire::test(SwitchApprovalAdmin::class, [
            'activeTab' => 'tab1',
            'attendanceRequests' => $attendanceRequests,
            ])
            ->assertSee('承認待ち')
            ->assertSee($user->name)
            ->assertSee($attendance->attend_start->locale('ja')->isoFormat('YYYY/MM/DD'))
            ->assertSee('修正申請のテスト')
            ->assertSee($modifiedAttendance->created_at->locale('ja')->isoFormat('YYYY/MM/DD'))
            ->assertSeeHtml('<a href="/admin/attendance/detail/' . $modifiedAttendance->attendance_id . '" class="approval-detail__link">詳細</a>', false);

        $administrator = new Administrator();
        $administrator->name = 'Admin';
        $administrator->email = 'admin@example.com';
        $administrator->password = bcrypt('password');
        $administrator->save();

        $response = $this->actingAs($administrator, 'admin')->get('/admin/attendance/detail/' . $modifiedAttendance->attendance_id);
        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($modifiedAttendance->attend_start->locale('ja')->isoFormat('YYYY年'));
        $response->assertSee($modifiedAttendance->attend_start->locale('ja')->isoFormat('M月D日'));
        $response->assertSee($modifiedAttendance->attend_start->locale('ja')->isoFormat('HH:mm'));
        $response->assertSee($modifiedAttendance->attend_end->locale('ja')->isoFormat('HH:mm'));
        $response->assertSee('修正申請のテスト');
    }

    //管理者の勤怠詳細画面にて修正申請の承認ができることの確認
    public function test_admin_attendance_detail_approval()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->attend_start = now()->subHours(2);
        $attendance->attend_end = now();
        $attendance->save();

        $response = $this->post(route('attendance_modify', ['id' => $attendance->id]), [
            'attend_start' => now()->subHours(1)->format('H:i'),
            'attend_end' => now()->format('H:i'),
            'comment' => '修正申請のテスト',
        ]);

        $response->assertStatus(302);

        $modifiedAttendance = ModifiedAttendance::where('attendance_id', $attendance->id)->first();

        $administrator = new Administrator();
        $administrator->name = 'Admin';
        $administrator->email = 'admin@example.com';
        $administrator->password = bcrypt('password');
        $administrator->save();

        $response = $this->actingAs($administrator, 'admin')->get('/admin/attendance/detail/' . $modifiedAttendance->attendance_id);
        $response->assertDontSee('承認済み');
        $response->assertSee('<a href="/admin/stamp_correction_request/approve/' . $modifiedAttendance->id . '" class="attendance-detail-link__content">承認</a>', false);
        $response = $this->actingAs($administrator, 'admin')->get('/admin/stamp_correction_request/approve/' . $modifiedAttendance->id);
        $response->assertStatus(302);
        $response = $this->actingAs($administrator, 'admin')->get('/admin/attendance/detail/' . $modifiedAttendance->attendance_id);
        $response->assertSee('承認済み');
    }
}
