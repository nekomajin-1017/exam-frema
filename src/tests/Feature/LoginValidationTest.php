<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginValidationTest extends TestCase
{
    use RefreshDatabase;

    private function validInput(array $overrides = []) {
        return array_merge([
            'email' => 'login-user@example.com',
            'password' => 'Coachtech777',
        ], $overrides);
    }

    // 【評価項目ID:2】ログイン時にメールアドレス未入力で送信した場合、バリデーションエラーが返るかを検証
    public function test_requires_email() {
        $response = $this->from('/login')->post('/login', $this->validInput([
            'email' => '',
        ]));

        $response
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email']);

        $this->assertSame('メールアドレスを入力してください', session('errors')->first('email'));
    }

    // 【評価項目ID:2】ログイン時にパスワード未入力で送信した場合、バリデーションエラーが返るかを検証
    public function test_requires_password() {
        $response = $this->from('/login')->post('/login', $this->validInput([
            'password' => '',
        ]));

        $response
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['password']);

        $this->assertSame('パスワードを入力してください', session('errors')->first('password'));
    }

    // 【評価項目ID:2】未登録メールアドレスでログインした場合、認証失敗メッセージが表示され未ログイン状態が維持されるかを検証
    public function test_shows_error_for_invalid_login() {
        $response = $this->from('/login')->post('/login', [
            'email' => 'not-registered@example.com',
            'password' => 'Coachtech777',
        ]);

        $response
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email']);

        $this->assertSame(__('auth.failed'), session('errors')->first('email'));
        $this->assertGuest();
    }

}
