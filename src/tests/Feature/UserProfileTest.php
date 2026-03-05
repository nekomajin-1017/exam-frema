<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_プロフィール情報と出品購入一覧表示(): void
    {
        $user = $this->createVerifiedUser('user');
        $seller = $this->createVerifiedUser('seller');
        $payment = $this->createPayment();
        $this->createProfile($user->id, 'profiles/user.png', 'ユーザー表示名', '060-0001', '北海道札幌市中央区北一条西2-2-2');

        $sellItem = $this->createItem($user->id, '出品商品A');
        $buyItem = $this->createItem($seller->id, '購入商品B');

        Order::create([
            'buyer_id' => $user->id,
            'item_id' => $buyItem->id,
            'checkout_session_id' => 'sess_user_profile_test',
            'total_price' => $buyItem->price,
            'payment_method_id' => $payment->id,
            'shipping_postal_code' => '060-0001',
            'shipping_address' => '北海道札幌市中央区北一条西2-2-2',
            'shipping_building' => '札幌フロント7F',
        ]);

        $buyResponse = $this->actingAs($user)->get(route('mypage', ['page' => 'buy']));
        $sellResponse = $this->actingAs($user)->get(route('mypage', ['page' => 'sell']));

        $buyResponse->assertOk();
        $buyResponse->assertSeeText($user->name);
        $buyResponse->assertSee(asset('storage/profiles/user.png'), false);
        $buyResponse->assertSeeText($buyItem->name);

        $sellResponse->assertOk();
        $sellResponse->assertSeeText($sellItem->name);
    }

    public function test_プロフィール編集の初期値表示(): void
    {
        $user = $this->createVerifiedUser('user');
        $profile = $this->createProfile(
            $user->id,
            'profiles/default.png',
            '初期表示ユーザー名',
            '980-0001',
            '宮城県仙台市青葉区中央1-1-1'
        );

        $response = $this->actingAs($user)->get(route('mypage.profile'));

        $response->assertOk();
        $response->assertSee(asset('storage/' . $profile->image_path), false);
        $response->assertSee('name="name"', false);
        $response->assertSee('value="初期表示ユーザー名"', false);
        $response->assertSee('name="postal_code"', false);
        $response->assertSee('value="980-0001"', false);
        $response->assertSee('name="address"', false);
        $response->assertSee('value="宮城県仙台市青葉区中央1-1-1"', false);
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
            'price' => 1200,
            'item_condition' => '良好',
            'is_sold' => false,
            'image_path' => 'dummy.jpg',
        ]);
    }

    private function createPayment(): Payment
    {
        return Payment::create([
            'name' => Payment::NAME_CARD,
            'stripe_method_type' => Payment::TYPE_CARD,
        ]);
    }

    private function createProfile(
        int $userId,
        ?string $imagePath,
        string $displayName,
        ?string $postalCode,
        ?string $address
    ): Profile {
        return Profile::create([
            'user_id' => $userId,
            'image_path' => $imagePath,
            'display_name' => $displayName,
            'postal_code' => $postalCode,
            'address' => $address,
            'building' => 'ABCビル3F',
        ]);
    }
}
