<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MylistTest extends TestCase
{
    use RefreshDatabase;

    public function test_いいね商品のみ表示(): void
    {
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

    public function test_購入済みはSold表示(): void
    {
        $currentUser = $this->createUser('current');
        $seller = $this->createUser('seller');
        $soldItem = $this->createItem($seller->id, '購入済み商品', true);

        Favorite::create([
            'user_id' => $currentUser->id,
            'item_id' => $soldItem->id,
        ]);

        $response = $this->actingAs($currentUser)->get(route('home', ['tab' => 'mylist']));

        $response->assertOk();
        $response->assertSeeText($soldItem->name);
        $response->assertSeeText('Sold');
    }

    public function test_未認証は空表示(): void
    {
        $seller = $this->createUser('seller');
        $item = $this->createItem($seller->id, '表示されない商品');

        $response = $this->get(route('home', ['tab' => 'mylist']));

        $response->assertOk();
        $response->assertSeeText('商品がありません。');
        $response->assertDontSeeText($item->name);
    }

    private function createUser(string $name): User
    {
        return User::create([
            'name' => $name,
            'email' => $name . '@example.com',
            'password' => Hash::make('Coachtech777'),
        ]);
    }

    private function createItem(int $userId, string $name, bool $isSold = false): Item
    {
        return Item::create([
            'user_id' => $userId,
            'name' => $name,
            'brand' => null,
            'description' => 'テスト商品説明',
            'price' => 1000,
            'item_condition' => '良好',
            'is_sold' => $isSold,
            'image_path' => 'dummy.jpg',
        ]);
    }
}
