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

    private function validRegisterInput(array $overrides = []): array
    {
        return array_merge([
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => 'Coachtech777',
            'password_confirmation' => 'Coachtech777',
        ], $overrides);
    }

    public function test_登録成功(): void
    {
        $input = $this->validRegisterInput([
            'email' => 'success@example.com',
        ]);

        $response = $this->post('/register', $input);

        $response->assertRedirect(route('verification.notice'));

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
    }

    public function test_会員登録後に認証メール送信される(): void
    {
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

    public function test_認証はこちらからでメール認証サイト遷移(): void
    {
        $user = User::create([
            'name' => '未認証ユーザー',
            'email' => 'unverified@example.com',
            'password' => Hash::make('Coachtech777'),
        ]);

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertOk();
        $response->assertSee('認証はこちらから');
        $response->assertSee('href="http://localhost:8025"', false);
    }

    public function test_認証完了でプロフィール設定画面遷移(): void
    {
        $input = $this->validRegisterInput([
            'email' => 'verify-flow@example.com',
        ]);

        $registerResponse = $this->post('/register', $input);
        $registerResponse->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'verify-flow@example.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->hasVerifiedEmail());

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

    public function test_正しい情報でログイン処理実行(): void
    {
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

    public function test_ログアウト処理実行(): void
    {
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
