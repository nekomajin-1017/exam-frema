<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestModels;
use Tests\TestCase;

class CommentsTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // 【評価項目ID:9】ログイン済みユーザーがコメント投稿すると、comments テーブルへ保存され商品詳細のコメント件数に反映されるかを検証
    public function test_allows_comment_for_user() {
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

    // 【評価項目ID:9】未ログインユーザーがコメント投稿を試みた場合、ログイン画面へリダイレクトされコメントは保存されないかを検証
    public function test_rejects_comment_for_guest() {
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

    // 【評価項目ID:9】コメント本文が空文字で投稿された場合、バリデーションエラーとなりコメントが1件も作成されないかを検証
    public function test_requires_comment_body() {
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

    // 【評価項目ID:9】256文字のコメントを投稿した場合、255文字上限のバリデーションエラーとなり保存されないかを検証
    public function test_rejects_comment_over_255_chars() {
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

}
