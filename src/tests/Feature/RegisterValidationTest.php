<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterValidationTest extends TestCase
{
    use RefreshDatabase;

    private function validInput(array $overrides = []): array
    {
        return array_merge([
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => 'Coachtech777',
            'password_confirmation' => 'Coachtech777',
        ], $overrides);
    }

    public function test_名前必須(): void
    {
        $response = $this->from('/register')->post('/register', $this->validInput([
            'name' => '',
            'email' => 'name-required@example.com',
        ]));

        $response
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['name']);

        $this->assertSame('お名前を入力してください', session('errors')->first('name'));
    }

    public function test_メール必須(): void
    {
        $response = $this->from('/register')->post('/register', $this->validInput([
            'email' => '',
        ]));

        $response
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['email']);

        $this->assertSame('メールアドレスを入力してください', session('errors')->first('email'));
    }

    public function test_パスワード必須(): void
    {
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

    public function test_パスワード8文字以上(): void
    {
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

    public function test_確認用パスワード一致(): void
    {
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
