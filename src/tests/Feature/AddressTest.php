<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Payment;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Stripe\Checkout\Session;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_住所変更は購入画面に反映(): void
    {
        $buyer = $this->createVerifiedUser('buyer');
        $seller = $this->createVerifiedUser('seller');
        $item = $this->createItem($seller->id, 'テスト商品');
        $this->createPayment(Payment::NAME_KONBINI, Payment::TYPE_KONBINI);

        $storeResponse = $this->actingAs($buyer)->post(route('purchase.address.store', $item), [
            'postal_code' => '460-0008',
            'address' => '愛知県名古屋市中区栄3-3-3',
            'building' => '栄プラザ8F',
        ]);

        $storeResponse->assertRedirect(route('purchase.show', $item));

        $purchaseResponse = $this->actingAs($buyer)->get(route('purchase.show', $item));

        $purchaseResponse->assertOk();
        $purchaseResponse->assertSeeText('〒460-0008');
        $purchaseResponse->assertSeeText('愛知県名古屋市中区栄3-3-3');
        $purchaseResponse->assertSeeText('栄プラザ8F');
    }

    public function test_購入商品に送付先住所が紐づく(): void
    {
        $buyer = $this->createVerifiedUser('buyer');
        $seller = $this->createVerifiedUser('seller');
        $item = $this->createItem($seller->id, '購入商品');
        $payment = $this->createPayment(Payment::NAME_KONBINI, Payment::TYPE_KONBINI);

        $this->actingAs($buyer)->post(route('purchase.address.store', $item), [
            'postal_code' => '810-0001',
            'address' => '福岡県福岡市中央区天神2-2-2',
            'building' => '天神スクエア5F',
        ]);

        $this->mock(CheckoutService::class, function (MockInterface $mock) use ($payment) {
            $mock->shouldReceive('findPaymentMethod')
                ->once()
                ->with($payment->id)
                ->andReturn($payment);

            $mock->shouldReceive('createSession')
                ->once()
                ->andReturn(Session::constructFrom([
                    'id' => 'sess_address_test',
                    'url' => 'https://example.test/checkout-address',
                ]));
        });

        $purchaseResponse = $this->actingAs($buyer)->post(route('purchase.store', $item), [
            'payment_method_id' => $payment->id,
            'shipping_address' => '1',
        ]);

        $purchaseResponse->assertRedirect('https://example.test/checkout-address');
        $this->assertDatabaseHas('orders', [
            'buyer_id' => $buyer->id,
            'item_id' => $item->id,
            'shipping_postal_code' => '810-0001',
            'shipping_address' => '福岡県福岡市中央区天神2-2-2',
            'shipping_building' => '天神スクエア5F',
        ]);
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

    private function createItem(int $sellerId, string $name): Item
    {
        return Item::create([
            'user_id' => $sellerId,
            'name' => $name,
            'brand' => 'テストブランド',
            'description' => 'テスト商品説明',
            'price' => 1200,
            'item_condition' => '良好',
            'is_sold' => false,
            'image_path' => 'dummy.jpg',
        ]);
    }

    private function createPayment(string $name, string $type): Payment
    {
        return Payment::create([
            'name' => $name,
            'stripe_method_type' => $type,
        ]);
    }
}
