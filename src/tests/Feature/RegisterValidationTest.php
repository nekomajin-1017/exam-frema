<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterValidationTest extends TestCase
{
    use RefreshDatabase;

    private function validInput(array $overrides = []) {
        return array_merge([
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => 'Coachtech777',
            'password_confirmation' => 'Coachtech777',
        ], $overrides);
    }

    // 【評価項目ID:1】会員登録時に名前未入力で送信した場合、name フィールドの必須エラーが返るかを検証
    public function test_requires_name() {
        $response = $this->from('/register')->post('/register', $this->validInput([
            'name' => '',
            'email' => 'name-required@example.com',
        ]));

        $response
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['name']);

        $this->assertSame('お名前を入力してください', session('errors')->first('name'));
    }

    // 【評価項目ID:1】会員登録時にメールアドレス未入力で送信した場合、email フィールドの必須エラーが返るかを検証
    public function test_requires_email() {
        $response = $this->from('/register')->post('/register', $this->validInput([
            'email' => '',
        ]));

        $response
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['email']);

        $this->assertSame('メールアドレスを入力してください', session('errors')->first('email'));
    }

    // 【評価項目ID:1】会員登録時にパスワードと確認用パスワードを未入力で送信した場合、password 必須エラーが返るかを検証
    public function test_requires_password() {
        $response = $this->from('/register')->post('/register', $this->validInput([
            'password' => '',
            'password_confirmation' => '',
            'email' => 'password-required@example.com',
        ]));

        $response
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['password']);

        $this->assertSame('パスワードを入力してください', session('errors')->first('password'));
    }

    // 【評価項目ID:1】会員登録時に8文字未満のパスワードを送信した場合、最小文字数エラーが返るかを検証
    public function test_requires_password_min_length() {
        $response = $this->from('/register')->post('/register', $this->validInput([
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
            'email' => 'password-min@example.com',
        ]));

        $response
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['password']);

        $this->assertSame('パスワードは8文字以上で入力してください', session('errors')->first('password'));
    }

    // 【評価項目ID:1】会員登録時に確認用パスワード不一致で送信した場合、一致チェックエラーが返るかを検証
    public function test_requires_password_match() {
        $response = $this->from('/register')->post('/register', $this->validInput([
            'password' => 'Coachtech777',
            'password_confirmation' => 'password999',
            'email' => 'password-confirmed@example.com',
        ]));

        $response
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['password']);

        $this->assertSame('パスワードと一致しません', session('errors')->first('password'));
    }

}
