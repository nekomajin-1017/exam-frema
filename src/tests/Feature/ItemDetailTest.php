<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Favorite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestModels;
use Tests\TestCase;

class ItemDetailTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // 【評価項目ID:7】商品詳細画面で商品基本情報・カテゴリ・コメント件数/本文・いいね件数が正しく表示されるかを検証
    public function test_shows_item_details() {
        $seller = $this->createUser('seller');
        $commentUser = $this->createUser('commenter');
        $favoriteUser1 = $this->createUser('fav1');
        $favoriteUser2 = $this->createUser('fav2');
        $item = $this->createItem($seller->id, 'レザー財布', ['price' => 1200]);
        $category = Category::create(['name' => 'ファッション']);
        $item->categories()->attach($category->id);

        Favorite::create(['user_id' => $favoriteUser1->id, 'item_id' => $item->id]);
        Favorite::create(['user_id' => $favoriteUser2->id, 'item_id' => $item->id]);

        Comment::create([
            'user_id' => $commentUser->id,
            'item_id' => $item->id,
            'comment' => '状態が良さそうです',
        ]);
        Comment::create([
            'user_id' => $seller->id,
            'item_id' => $item->id,
            'comment' => 'ご検討ください',
        ]);

        $response = $this->get(route('item.show', $item));

        $response->assertOk();
        $response->assertSee('alt="' . $item->name . '"', false);
        $response->assertSeeText($item->name);
        $response->assertSeeText('ブランド名 ' . $item->brand);
        $response->assertSeeText('￥1,200');
        $response->assertSeeText($item->description);
        $response->assertSeeText($category->name);
        $response->assertSeeText($item->item_condition);
        $response->assertSeeText('コメント(2)');
        $response->assertSeeText($commentUser->name);
        $response->assertSeeText('状態が良さそうです');
        $response->assertSee('<span class="item-stat-count">2</span>', false);
    }

    // 【評価項目ID:7】商品に複数カテゴリが紐づく場合、詳細画面で全カテゴリ名が表示されるかを検証
    public function test_shows_all_categories() {
        $seller = $this->createUser('seller');
        $item = $this->createItem($seller->id, 'スニーカー', ['price' => 3000]);
        $category1 = Category::create(['name' => 'メンズ']);
        $category2 = Category::create(['name' => '靴']);
        $item->categories()->attach([$category1->id, $category2->id]);

        $response = $this->get(route('item.show', $item));

        $response->assertOk();
        $response->assertSeeText($category1->name);
        $response->assertSeeText($category2->name);
    }

}
