<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_全商品表示(): void
    {
        $seller1 = $this->createUser('seller1');
        $seller2 = $this->createUser('seller2');

        $item1 = $this->createItem($seller1->id, '商品A');
        $item2 = $this->createItem($seller2->id, '商品B');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSeeText($item1->name);
        $response->assertSeeText($item2->name);
    }

    public function test_購入済みはSold表示(): void
    {
        $seller = $this->createUser('seller');
        $buyer = $this->createUser('buyer');
        $item = $this->createItem($seller->id, '購入済み商品');
        $payment = Payment::create([
            'name' => Payment::NAME_CARD,
            'stripe_method_type' => Payment::TYPE_CARD,
        ]);

        Order::create([
            'buyer_id' => $buyer->id,
            'item_id' => $item->id,
            'checkout_session_id' => 'sess_123',
            'total_price' => $item->price,
            'payment_method_id' => $payment->id,
            'shipping_postal_code' => '530-0001',
            'shipping_address' => '大阪府大阪市北区梅田1-1-1',
            'shipping_building' => '梅田センタービル10F',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSeeText($item->name);
        $response->assertSeeText('Sold');
    }

    public function test_自分の商品は非表示(): void
    {
        $currentUser = $this->createUser('current');
        $otherUser = $this->createUser('other');

        $ownItem = $this->createItem($currentUser->id, '自分の商品');
        $otherItem = $this->createItem($otherUser->id, '他人の商品');

        $response = $this->actingAs($currentUser)->get(route('home'));

        $response->assertOk();
        $response->assertDontSeeText($ownItem->name);
        $response->assertSeeText($otherItem->name);
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
