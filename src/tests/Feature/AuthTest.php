<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function validRegisterInput(array $overrides = []) {
        return array_merge([
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => 'Coachtech777',
            'password_confirmation' => 'Coachtech777',
        ], $overrides);
    }

    // 【評価項目ID:1,16】名前・メール・パスワードで会員登録後、認証画面を経由してプロフィール設定画面へ遷移するかを検証
    public function test_registers_user() {
        $input = $this->validRegisterInput([
            'email' => 'success@example.com',
        ]);

        $registerResponse = $this->post('/register', $input);

        $registerResponse->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'success@example.com',
        ]);

        $user = User::where('email', 'success@example.com')->first();
        $this->assertNotNull($user);
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'display_name' => 'テスト太郎',
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $verifyResponse = $this->actingAs($user)->get($verificationUrl);

        $verifyResponse->assertRedirect('/mypage/profile');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

    }

    // 【評価項目ID:1,16】会員登録完了時に VerifyEmail 通知が対象ユーザーへ1通送信されるかを検証
    public function test_sends_verification_email() {
        Notification::fake();

        $input = $this->validRegisterInput([
            'email' => 'verify-mail@example.com',
        ]);

        $response = $this->post('/register', $input);
        $response->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'verify-mail@example.com')->first();
        $this->assertNotNull($user);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    // 【評価項目ID:16】メール認証案内画面に「認証はこちらから」リンクが表示され、Mail UI のURLが設定値どおり埋め込まれるかを検証
    public function test_shows_mail_link_on_verification_notice() {
        $user = User::create([
            'name' => '未認証ユーザー',
            'email' => 'unverified@example.com',
            'password' => Hash::make('Coachtech777'),
        ]);

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertOk();
        $response->assertSee('認証はこちらから');
        $response->assertSee('href="' . config('services.mail_ui_url') . '"', false);
    }

    // 【評価項目ID:2】認証済みユーザーが正しい認証情報でログインするとトップページへ遷移し認証状態になるかを検証
    public function test_logs_in_user() {
        $user = User::create([
            'name' => 'ログイン太郎',
            'email' => 'login-user@example.com',
            'password' => Hash::make('Coachtech777'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $response = $this->post('/login', [
            'email' => 'login-user@example.com',
            'password' => 'Coachtech777',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    // 【評価項目ID:3】ログイン中ユーザーがログアウトするとトップページへ遷移し、ゲスト状態へ戻るかを検証
    public function test_logs_out_user() {
        $user = User::create([
            'name' => 'ログアウト太郎',
            'email' => 'logout-user@example.com',
            'password' => Hash::make('Coachtech777'),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
