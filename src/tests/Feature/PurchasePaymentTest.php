<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Payment;
use App\Models\Profile;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Stripe\Checkout\Session;
use Tests\TestCase;

class PurchasePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_購入ボタンで購入完了(): void
    {
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

    public function test_購入商品は一覧でSold表示(): void
    {
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

    public function test_購入商品はプロフィール購入一覧に追加(): void
    {
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

    public function test_支払い方法選択は小計に反映(): void
    {
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
        $purchasePage->assertSee('<strong>クレジットカード</strong>', false);
    }

    private function mockCheckout(Payment $payment, string $sessionId, string $sessionUrl): void
    {
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

    private function createProfile(int $userId): Profile
    {
        return Profile::create([
            'user_id' => $userId,
            'display_name' => '表示名',
            'postal_code' => '650-0001',
            'address' => '兵庫県神戸市中央区加納町4-4-4',
            'building' => '神戸タワー12F',
        ]);
    }
}
