<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    //ユーザー情報登録
    public function test_register_user()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => "テストユーザ",
            'email' => "test@example.com",
            'password' => "password",
            'password_confirmation' => "password",
        ]);

        //データベースにユーザーが登録されていることを確認
        $this->assertDatabaseHas(User::class, [
            'name' => "テストユーザ",
            'email' => "test@example.com",
        ]);

        //登録したメールアドレス宛に認証メールが送信されていることを確認
        $user = User::where('email', 'test@example.com')->first();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    //メール認証サイト（MailHog）の表示確認
    public function test_mailhog_link_display()
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');
        $response->assertStatus(200);
        $response->assertSee('認証はこちらから');
        $response->assertSee('href="http://localhost:8025"', false);
    }

    //メール認証完了後の勤怠画面へのリダイレクト確認
    public function test_verified_user_is_redirected_to_home_after_email_verification()
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect('/attendance?verified=1');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    //名前バリデーション
    public function test_register_user_validate_name()
    {
        $response = $this->post('/register', [
            'name' => "",
            'email' => "test@example.com",
            'password' => "password",
            'password_confirmation' => "password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');

        $errors = session('errors');
        $this->assertEquals('お名前を入力してください', $errors->first('name'));
    }

    //メールアドレスバリデーション
    public function test_register_user_validate_email()
    {
        $response = $this->post('/register', [
            'name' => "テストユーザ",
            'email' => "",
            'password' => "password",
            'password_confirmation' => "password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    //パスワードバリデーション
    public function test_register_user_validate_password()
    {
        $response = $this->post('/register', [
            'name' => "テストユーザ",
            'email' => "test@example.com",
            'password' => "",
            'password_confirmation' => "password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    //パスワード文字数バリデーション
    public function test_register_user_validate_password_under7()
    {
        $response = $this->post('/register', [
            'name' => "テストユーザ",
            'email' => "test@example.com",
            'password' => "pass",
            'password_confirmation' => "pass",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードは8文字以上で入力してください', $errors->first('password'));
    }

    //パスワード確認バリデーション
    public function test_register_user_validate_confirm_password()
    {
        $response = $this->post('/register', [
            'name' => "テストユーザ",
            'email' => "test@example.com",
            'password' => "password",
            'password_confirmation' => "password123",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードと一致しません', $errors->first('password'));
    }

}