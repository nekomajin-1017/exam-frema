<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ItemDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_商品詳細情報表示(): void
    {
        $seller = $this->createUser('seller');
        $commentUser = $this->createUser('commenter');
        $favoriteUser1 = $this->createUser('fav1');
        $favoriteUser2 = $this->createUser('fav2');
        $item = $this->createItem($seller->id, 'レザー財布', 1200);
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

    public function test_複数カテゴリ表示(): void
    {
        $seller = $this->createUser('seller');
        $item = $this->createItem($seller->id, 'スニーカー', 3000);
        $category1 = Category::create(['name' => 'メンズ']);
        $category2 = Category::create(['name' => '靴']);
        $item->categories()->attach([$category1->id, $category2->id]);

        $response = $this->get(route('item.show', $item));

        $response->assertOk();
        $response->assertSeeText($category1->name);
        $response->assertSeeText($category2->name);
    }

    private function createUser(string $name): User
    {
        return User::create([
            'name' => $name,
            'email' => $name . '@example.com',
            'password' => Hash::make('Coachtech777'),
        ]);
    }

    private function createItem(int $userId, string $name, int $price): Item
    {
        return Item::create([
            'user_id' => $userId,
            'name' => $name,
            'brand' => 'テストブランド',
            'description' => 'テスト商品説明',
            'price' => $price,
            'item_condition' => '良好',
            'is_sold' => false,
            'image_path' => 'dummy.jpg',
        ]);
    }
}
