<?php

namespace Tests\Feature;

use App\Models\Favorite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestModels;
use Tests\TestCase;

class MylistTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // 【評価項目ID:5】マイリストタブでは、ログインユーザーがいいねした商品だけが表示されるかを検証
    public function test_shows_only_favorites() {
        $currentUser = $this->createUser('current');
        $seller = $this->createUser('seller');
        $likedItem = $this->createItem($seller->id, 'いいね済み商品');
        $notLikedItem = $this->createItem($seller->id, 'いいねしていない商品');

        Favorite::create([
            'user_id' => $currentUser->id,
            'item_id' => $likedItem->id,
        ]);

        $response = $this->actingAs($currentUser)->get(route('home', ['tab' => 'mylist']));

        $response->assertOk();
        $response->assertSeeText($likedItem->name);
        $response->assertDontSeeText($notLikedItem->name);
    }

    // 【評価項目ID:5】マイリスト内の売約済み商品に Sold ラベルが表示されるかを検証
    public function test_shows_sold_in_mylist() {
        $currentUser = $this->createUser('current');
        $seller = $this->createUser('seller');
        $soldItem = $this->createItem($seller->id, '購入済み商品', ['is_sold' => true]);

        Favorite::create([
            'user_id' => $currentUser->id,
            'item_id' => $soldItem->id,
        ]);

        $response = $this->actingAs($currentUser)->get(route('home', ['tab' => 'mylist']));

        $response->assertOk();
        $response->assertSeeText($soldItem->name);
        $response->assertSeeText('Sold');
    }

    // 【評価項目ID:5】未ログインでマイリストを開いた場合は空状態メッセージのみ表示され、商品は表示されないかを検証
    public function test_shows_empty_mylist_for_guest() {
        $seller = $this->createUser('seller');
        $item = $this->createItem($seller->id, '表示されない商品');

        $response = $this->get(route('home', ['tab' => 'mylist']));

        $response->assertOk();
        $response->assertSeeText('商品がありません。');
        $response->assertDontSeeText($item->name);
    }

}
