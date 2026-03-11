<?php

namespace Tests\Feature;

use App\Models\Favorite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestModels;
use Tests\TestCase;

class ItemSearchTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // 【評価項目ID:6】キーワードで商品名を部分一致検索したとき、合致する商品だけが一覧に表示されるかを検証
    public function test_searches_by_partial_name() {
        $seller = $this->createUser('seller');
        $matchedItem = $this->createItem($seller->id, '腕時計ブラック');
        $notMatchedItem = $this->createItem($seller->id, 'スニーカー');

        $response = $this->get(route('home', ['keyword' => '時計']));

        $response->assertOk();
        $response->assertSeeText($matchedItem->name);
        $response->assertDontSeeText($notMatchedItem->name);
    }

    // 【評価項目ID:6】検索後にマイリストへ遷移してもキーワードが保持され、いいね済み商品の絞り込み結果が維持されるかを検証
    public function test_keeps_keyword_in_mylist() {
        $user = $this->createUser('current');
        $seller = $this->createUser('seller');
        $matchedItem = $this->createItem($seller->id, '腕時計ブラック');
        $notMatchedItem = $this->createItem($seller->id, 'スニーカー');

        Favorite::create([
            'user_id' => $user->id,
            'item_id' => $matchedItem->id,
        ]);
        Favorite::create([
            'user_id' => $user->id,
            'item_id' => $notMatchedItem->id,
        ]);

        $searchResponse = $this->actingAs($user)->get(route('home', ['keyword' => '時計']));
        $mylistUrl = route('home', ['tab' => 'mylist', 'keyword' => '時計']);

        $searchResponse->assertOk();
        $searchResponse->assertSee('tab=mylist&amp;keyword=', false);

        $mylistResponse = $this->actingAs($user)->get($mylistUrl);

        $mylistResponse->assertOk();
        $mylistResponse->assertSee('name="keyword"', false);
        $mylistResponse->assertSee('value="時計"', false);
        $mylistResponse->assertSeeText($matchedItem->name);
        $mylistResponse->assertDontSeeText($notMatchedItem->name);
    }

}
