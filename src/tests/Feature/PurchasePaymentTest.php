<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Stripe\Checkout\Session;
use Tests\Concerns\CreatesTestModels;
use Tests\TestCase;

class PurchasePaymentTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // 【評価項目ID:10】購入実行時にチェックアウトセッション作成後のリダイレクト・注文作成・商品の売約状態更新が行われるかを検証
    public function test_completes_purchase() {
        $buyer = $this->createVerifiedUser('buyer');
        $seller = $this->createVerifiedUser('seller');
        $item = $this->createItem($seller->id, 'テスト商品');
        $payment = $this->createPayment(Payment::NAME_KONBINI, Payment::TYPE_KONBINI);
        $this->createProfile($buyer->id);

        $this->mockCheckout($payment, 'sess_purchase_done', 'https://example.test/checkout');

        $response = $this->actingAs($buyer)->post(route('purchase.store', $item), [
            'payment_method_id' => $payment->id,
            'shipping_address' => '1',
        ]);

        $response->assertRedirect('https://example.test/checkout');
        $this->assertDatabaseHas('orders', [
            'buyer_id' => $buyer->id,
            'item_id' => $item->id,
            'checkout_session_id' => 'sess_purchase_done',
            'payment_method_id' => $payment->id,
        ]);
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'is_sold' => true,
        ]);
    }

    // 【評価項目ID:10】購入後の商品がトップページ一覧で Sold ラベル付き表示になるかを検証
    public function test_shows_sold_on_index_after_purchase() {
        $buyer = $this->createVerifiedUser('buyer');
        $seller = $this->createVerifiedUser('seller');
        $item = $this->createItem($seller->id, 'Sold対象商品');
        $payment = $this->createPayment(Payment::NAME_KONBINI, Payment::TYPE_KONBINI);
        $this->createProfile($buyer->id);

        $this->mockCheckout($payment, 'sess_sold', 'https://example.test/checkout-sold');

        $this->actingAs($buyer)->post(route('purchase.store', $item), [
            'payment_method_id' => $payment->id,
            'shipping_address' => '1',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSeeText($item->name);
        $response->assertSeeText('Sold');
    }

    // 【評価項目ID:10】購入完了した商品がマイページの購入一覧（buy タブ）に表示されるかを検証
    public function test_adds_purchase_to_buy_list() {
        $buyer = $this->createVerifiedUser('buyer');
        $seller = $this->createVerifiedUser('seller');
        $item = $this->createItem($seller->id, '購入履歴商品');
        $payment = $this->createPayment(Payment::NAME_KONBINI, Payment::TYPE_KONBINI);
        $this->createProfile($buyer->id);

        $this->mockCheckout($payment, 'sess_profile_buy', 'https://example.test/checkout-buy');

        $this->actingAs($buyer)->post(route('purchase.store', $item), [
            'payment_method_id' => $payment->id,
            'shipping_address' => '1',
        ]);

        $response = $this->actingAs($buyer)->get(route('mypage', ['page' => 'buy']));

        $response->assertOk();
        $response->assertSeeText($item->name);
    }

    // 【評価項目ID:11】配送先未入力で購入失敗した場合でも、選択した支払い方法が購入画面上の表示に保持されるかを検証
    public function test_keeps_payment_selection_on_purchase_page() {
        $buyer = $this->createVerifiedUser('buyer');
        $seller = $this->createVerifiedUser('seller');
        $item = $this->createItem($seller->id, '支払い方法確認商品');
        $this->createPayment(Payment::NAME_KONBINI, Payment::TYPE_KONBINI);
        $cardPayment = $this->createPayment(Payment::NAME_CARD, Payment::TYPE_CARD);

        $response = $this->from(route('purchase.show', $item))
            ->actingAs($buyer)
            ->post(route('purchase.store', $item), [
                'payment_method_id' => $cardPayment->id,
            ]);

        $response->assertRedirect(route('purchase.show', $item));
        $response->assertSessionHasErrors([
            'shipping_address' => '配送先を入力してください',
        ]);

        $purchasePage = $this->actingAs($buyer)->get(route('purchase.show', $item));

        $purchasePage->assertOk();
        $purchasePage->assertSee('<strong>カード支払い</strong>', false);
    }

    private function mockCheckout(Payment $payment, string $sessionId, string $sessionUrl) {
        $this->mock(CheckoutService::class, function (MockInterface $mock) use ($payment, $sessionId, $sessionUrl) {
            $mock->shouldReceive('findPaymentMethod')
                ->once()
                ->with($payment->id)
                ->andReturn($payment);

            $mock->shouldReceive('createSession')
                ->once()
                ->andReturn(Session::constructFrom([
                    'id' => $sessionId,
                    'url' => $sessionUrl,
                ]));
        });
    }

}
