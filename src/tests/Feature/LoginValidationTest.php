<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginValidationTest extends TestCase
{
    use RefreshDatabase;

    private function validInput(array $overrides = []): array
    {
        return array_merge([
            'email' => 'login-user@example.com',
            'password' => 'Coachtech777',
        ], $overrides);
    }

    public function test_メール必須(): void
    {
        $response = $this->from('/login')->post('/login', $this->validInput([
            'email' => '',
        ]));

        $response
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email']);

        $this->assertSame('メールアドレスを入力してください', session('errors')->first('email'));
    }

    public function test_パスワード必須(): void
    {
        $response = $this->from('/login')->post('/login', $this->validInput([
            'password' => '',
        ]));

        $response
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['password']);

        $this->assertSame('パスワードを入力してください', session('errors')->first('password'));
    }

    public function test_認証失敗メッセージ(): void
    {
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
