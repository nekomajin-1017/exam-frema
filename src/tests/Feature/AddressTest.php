<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Stripe\Checkout\Session;
use Tests\Concerns\CreatesTestModels;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // 【評価項目ID:12】送付先住所を変更して保存すると、購入画面に郵便番号・住所・建物名が反映されるかを検証
    public function test_shows_updated_address_on_purchase() {
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

    // 【評価項目ID:12】購入確定時に、直前で保存した送付先住所が注文データへ正しく保存されるかを検証
    public function test_saves_shipping_address_on_order() {
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

}
