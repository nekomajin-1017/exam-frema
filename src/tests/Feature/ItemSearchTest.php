<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ItemSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_商品名で部分一致検索(): void
    {
        $seller = $this->createUser('seller');
        $matchedItem = $this->createItem($seller->id, '腕時計ブラック');
        $notMatchedItem = $this->createItem($seller->id, 'スニーカー');

        $response = $this->get(route('home', ['keyword' => '時計']));

        $response->assertOk();
        $response->assertSeeText($matchedItem->name);
        $response->assertDontSeeText($notMatchedItem->name);
    }

    public function test_検索状態はマイリストでも保持(): void
    {
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

    private function createUser(string $name): User
    {
        return User::create([
            'name' => $name,
            'email' => $name . '@example.com',
            'password' => Hash::make('Coachtech777'),
        ]);
    }

    private function createItem(int $userId, string $name): Item
    {
        return Item::create([
            'user_id' => $userId,
            'name' => $name,
            'brand' => null,
            'description' => 'テスト商品説明',
            'price' => 1000,
            'item_condition' => '良好',
            'is_sold' => false,
            'image_path' => 'dummy.jpg',
        ]);
    }
}
