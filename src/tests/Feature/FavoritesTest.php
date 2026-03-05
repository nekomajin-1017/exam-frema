<?php

namespace Tests\Feature;

use App\Models\Favorite;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FavoritesTest extends TestCase
{
    use RefreshDatabase;

    public function test_いいね登録と合計値増加(): void
    {
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

    public function test_いいね済みアイコン色変化(): void
    {
        $user = $this->createVerifiedUser('user');
        $seller = $this->createVerifiedUser('seller');
        $item = $this->createItem($seller->id, 'テスト商品');

        $this->actingAs($user)->post(route('item.favorite', $item));

        $response = $this->actingAs($user)->get(route('item.show', $item));
        $response->assertOk();
        $response->assertSee('heartLogoPink.png', false);
    }

    public function test_いいね解除と合計値減少(): void
    {
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
