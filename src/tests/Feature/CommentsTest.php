<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログイン済みはコメント送信可能(): void
    {
        $user = $this->createVerifiedUser('user');
        $seller = $this->createVerifiedUser('seller');
        $item = $this->createItem($seller->id, 'テスト商品');

        $this->actingAs($user)->post(route('item.comment', $item), [
            'comment' => 'コメント本文です',
        ]);

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'comment' => 'コメント本文です',
        ]);

        $response = $this->actingAs($user)->get(route('item.show', $item));
        $response->assertOk();
        $response->assertSeeText('コメント(1)');
    }

    public function test_未ログインはコメント送信不可(): void
    {
        $seller = $this->createVerifiedUser('seller');
        $item = $this->createItem($seller->id, 'テスト商品');

        $response = $this->post(route('item.comment', $item), [
            'comment' => 'ゲストコメント',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'comment' => 'ゲストコメント',
        ]);
    }

    public function test_コメント未入力はバリデーションエラー(): void
    {
        $user = $this->createVerifiedUser('user');
        $seller = $this->createVerifiedUser('seller');
        $item = $this->createItem($seller->id, 'テスト商品');

        $response = $this->from(route('item.show', $item))
            ->actingAs($user)
            ->post(route('item.comment', $item), [
                'comment' => '',
            ]);

        $response->assertRedirect(route('item.show', $item));
        $response->assertSessionHasErrors([
            'comment' => 'コメントが入力されていません',
        ]);
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_コメント255文字超過はバリデーションエラー(): void
    {
        $user = $this->createVerifiedUser('user');
        $seller = $this->createVerifiedUser('seller');
        $item = $this->createItem($seller->id, 'テスト商品');
        $tooLongComment = str_repeat('あ', 256);

        $response = $this->from(route('item.show', $item))
            ->actingAs($user)
            ->post(route('item.comment', $item), [
                'comment' => $tooLongComment,
            ]);

        $response->assertRedirect(route('item.show', $item));
        $response->assertSessionHasErrors([
            'comment' => 'コメントは255文字以内で入力してください',
        ]);
        $this->assertDatabaseCount('comments', 0);
    }

    private function createVerifiedUser(string $name): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $name . '@example.com',
            'password' => Hash::make('Coachtech777'),
        ]);

        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    private function createItem(int $userId, string $name): Item
    {
        return Item::create([
            'user_id' => $userId,
            'name' => $name,
            'brand' => 'テストブランド',
            'description' => 'テスト商品説明',
            'price' => 1000,
            'item_condition' => '良好',
            'is_sold' => false,
            'image_path' => 'dummy.jpg',
        ]);
    }
}
