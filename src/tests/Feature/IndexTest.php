<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestModels;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // 【評価項目ID:4】未ログイン状態のトップページで、登録済みの全商品が一覧に表示されるかを検証
    public function test_shows_all_items() {
        $seller1 = $this->createUser('seller1');
        $seller2 = $this->createUser('seller2');

        $item1 = $this->createItem($seller1->id, '商品A');
        $item2 = $this->createItem($seller2->id, '商品B');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSeeText($item1->name);
        $response->assertSeeText($item2->name);
    }

    // 【評価項目ID:4】売約済みフラグが立った商品は、トップページ上で Sold ラベル付きで表示されるかを検証
    public function test_shows_sold_for_sold_items() {
        $seller = $this->createUser('seller');
        $item = $this->createItem($seller->id, '購入済み商品', ['is_sold' => true]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSeeText($item->name);
        $response->assertSeeText('Sold');
    }

    // 【評価項目ID:4】ログインユーザー自身が出品した商品はトップページ一覧から除外され、他人の商品だけが表示されるかを検証
    public function test_hides_own_items() {
        $currentUser = $this->createUser('current');
        $otherUser = $this->createUser('other');

        $ownItem = $this->createItem($currentUser->id, '自分の商品');
        $otherItem = $this->createItem($otherUser->id, '他人の商品');

        $response = $this->actingAs($currentUser)->get(route('home'));

        $response->assertOk();
        $response->assertDontSeeText($ownItem->name);
        $response->assertSeeText($otherItem->name);
    }

}
