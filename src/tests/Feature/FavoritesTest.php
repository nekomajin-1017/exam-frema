<?php

namespace Tests\Feature;

use App\Models\Favorite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestModels;
use Tests\TestCase;

class FavoritesTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // 【評価項目ID:8】いいね操作で favorites レコードが追加され、商品詳細のいいね件数表示が1件に更新されるかを検証
    public function test_adds_favorite_and_updates_count() {
        $user = $this->createVerifiedUser('user');
        $seller = $this->createVerifiedUser('seller');
        $item = $this->createItem($seller->id, 'テスト商品');

        $this->actingAs($user)->post(route('item.favorite', $item));

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->actingAs($user)->get(route('item.show', $item));
        $response->assertOk();
        $response->assertSee('<span class="item-stat-count">1</span>', false);
    }

    // 【評価項目ID:8】いいね済み商品では詳細画面のハートアイコンがアクティブ状態の画像に切り替わるかを検証
    public function test_shows_active_favorite_icon() {
        $user = $this->createVerifiedUser('user');
        $seller = $this->createVerifiedUser('seller');
        $item = $this->createItem($seller->id, 'テスト商品');

        $this->actingAs($user)->post(route('item.favorite', $item));

        $response = $this->actingAs($user)->get(route('item.show', $item));
        $response->assertOk();
        $response->assertSee('heartLogoPink.png', false);
    }

    // 【評価項目ID:8】再度いいね操作すると解除され、favorites レコード削除・非アクティブアイコン表示・件数0表示になるかを検証
    public function test_removes_favorite_and_updates_count() {
        $user = $this->createVerifiedUser('user');
        $seller = $this->createVerifiedUser('seller');
        $item = $this->createItem($seller->id, 'テスト商品');
        Favorite::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->actingAs($user)->post(route('item.favorite', $item));

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->actingAs($user)->get(route('item.show', $item));
        $response->assertOk();
        $response->assertSee('heartLogoDefault.png', false);
        $response->assertDontSee('heartLogoPink.png', false);
        $response->assertSee('<span class="item-stat-count">0</span>', false);
    }

}
