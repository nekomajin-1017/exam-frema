<?php

namespace Tests\Feature;

use App\Models\Order;
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

    // 【実装保全のため】購入成功コールバックで完了処理後に商品一覧へリダイレクトされるかを検証
    public function test_redirects_purchase_success_to_home() {
        $this->mock(CheckoutService::class, function (MockInterface $mock) {
            $mock->shouldReceive('completeOrderBySessionId')
                ->once()
                ->withArgs(function (string $sessionId, $orders, $profiles, bool $forceCreateForKonbini) {
                    return $sessionId === 'sess_success_route' && $forceCreateForKonbini === true;
                });
        });

        $response = $this->get(route('purchase.success', [
            'session_id' => 'sess_success_route',
        ]));

        $response->assertRedirect(route('home'));
    }

    // 【実装保全のため】購入キャンセル時は未払いコンビニ注文だけを取り消し、売約状態も戻すかを検証
    public function test_cancels_only_unpaid_konbini_order() {
        $buyer = $this->createVerifiedUser('buyer');
        $seller = $this->createVerifiedUser('seller');
        $unpaidItem = $this->createItem($seller->id, '未払いコンビニ商品', ['is_sold' => true]);
        $paidItem = $this->createItem($seller->id, '支払い済みコンビニ商品', ['is_sold' => true]);
        $cardItem = $this->createItem($seller->id, 'カード決済商品', ['is_sold' => true]);
        $konbiniPayment = $this->createPayment(Payment::NAME_KONBINI, Payment::TYPE_KONBINI);
        $cardPayment = $this->createPayment(Payment::NAME_CARD, Payment::TYPE_CARD);
        $profile = $this->createProfile($buyer->id);

        Order::create([
            'buyer_id' => $buyer->id,
            'item_id' => $unpaidItem->id,
            'checkout_session_id' => 'sess_unpaid',
            'total_price' => $unpaidItem->price,
            'payment_method_id' => $konbiniPayment->id,
            'shipping_postal_code' => $profile->postal_code,
            'shipping_address' => $profile->address,
            'shipping_building' => $profile->building,
        ]);
        Order::create([
            'buyer_id' => $buyer->id,
            'item_id' => $paidItem->id,
            'checkout_session_id' => 'sess_paid',
            'total_price' => $paidItem->price,
            'payment_method_id' => $konbiniPayment->id,
            'shipping_postal_code' => $profile->postal_code,
            'shipping_address' => $profile->address,
            'shipping_building' => $profile->building,
        ]);
        Order::create([
            'buyer_id' => $buyer->id,
            'item_id' => $cardItem->id,
            'checkout_session_id' => 'sess_card',
            'total_price' => $cardItem->price,
            'payment_method_id' => $cardPayment->id,
            'shipping_postal_code' => $profile->postal_code,
            'shipping_address' => $profile->address,
            'shipping_building' => $profile->building,
        ]);

        $this->mock(CheckoutService::class, function (MockInterface $mock) {
            $mock->shouldReceive('isSessionPaid')
                ->once()
                ->with('sess_unpaid')
                ->andReturn(false);
        });

        $response = $this->actingAs($buyer)->get(route('purchase.cancel', [
            'item_id' => $unpaidItem->id,
        ]));

        $response->assertRedirect(route('home'));
        $this->assertDatabaseMissing('orders', [
            'item_id' => $unpaidItem->id,
        ]);
        $this->assertDatabaseHas('items', [
            'id' => $unpaidItem->id,
            'is_sold' => false,
        ]);
        $this->assertDatabaseHas('orders', [
            'item_id' => $paidItem->id,
            'checkout_session_id' => 'sess_paid',
        ]);
        $this->assertDatabaseHas('items', [
            'id' => $paidItem->id,
            'is_sold' => true,
        ]);
        $this->assertDatabaseHas('orders', [
            'item_id' => $cardItem->id,
            'checkout_session_id' => 'sess_card',
        ]);
        $this->assertDatabaseHas('items', [
            'id' => $cardItem->id,
            'is_sold' => true,
        ]);
    }

    // 【実装保全のため】未認証メールユーザーは購入・出品・マイページ・コメント・いいねの保護ルートへ入れないかを検証
    public function test_unverified_user_cannot_access_verified_routes() {
        $user = $this->createUser('unverified');
        $seller = $this->createVerifiedUser('seller');
        $item = $this->createItem($seller->id, '保護対象商品');

        $this->actingAs($user)
            ->get(route('purchase.show', $item))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->get(route('sell.show'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->get(route('mypage'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->post(route('item.comment', $item), [
                'comment' => '未認証コメント',
            ])
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->post(route('item.favorite', $item))
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('comments', 0);
        $this->assertDatabaseCount('favorites', 0);
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
