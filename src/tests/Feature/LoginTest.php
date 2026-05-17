<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Administrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    //ログイン機能
    public function test_login_user()
    {        $user = User::factory()->create([
            'email' => "test@example.com",
            'password' => bcrypt("password"),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/attendance');
        $this->assertAuthenticatedAs($user);
    }

    //メールアドレスバリデーション
    public function test_login_user_validate_email()
    {
        $response = $this->post('/login', [
            'email' => "",
            'password' => "password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    //パスワードバリデーション
    public function test_login_user_validate_password()
    {        $response = $this->post('/login', [
            'email' => "test@example.com",
            'password' => "",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    //ログイン情報不一致
    public function test_login_user_invalid_credentials()
    {        $response = $this->post('/login', [
            'email' => "test@example.com",
            'password' => "wrongpassword",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $errors = session('errors');
        $this->assertEquals('ログイン情報が登録されていません', $errors->first('email'));
    }

    //管理者ログインのリダイレクト確認
    public function test_guest_is_redirected_to_admin_login()
    {
        $response = $this->get('/admin/attendance/list');

        $response->assertRedirect(route('login_admin'));
    }

    //管理者ログイン機能
    public function test_admin_can_login()
    {
        $admin = new Administrator();
        $admin->name = "テスト管理者";
        $admin->email = "admin@example.com";
        $admin->password = bcrypt('password');
        $admin->save();

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/attendance/list');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    //管理者認証でのメールアドレスバリデーション
    public function test_admin_login_validate_email()
    {
        $response = $this->post('/admin/login', [
            'email' => "",
            'password' => "password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    //管理者認証でのパスワードバリデーション
    public function test_admin_login_validate_password()
    {
        $response = $this->post('/admin/login', [
            'email' => "admin@example.com",
            'password' => "",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }
}
