<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTestModels;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // 【評価項目ID:13】マイページの購入一覧でプロフィール情報と購入商品が表示され、出品一覧で自分の出品商品が表示されるかを検証
    public function test_shows_profile_and_lists() {
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
        $buyItem->update(['is_sold' => true]);

        $buyResponse = $this->actingAs($user)->get(route('mypage', ['page' => 'buy']));
        $sellResponse = $this->actingAs($user)->get(route('mypage', ['page' => 'sell']));

        $buyResponse->assertOk();
        $buyResponse->assertSeeText($user->name);
        $buyResponse->assertSee('/storage/profiles/user.png', false);
        $buyResponse->assertSeeText($buyItem->name);

        $sellResponse->assertOk();
        $sellResponse->assertSeeText($sellItem->name);
    }

    // 【評価項目ID:14】プロフィール編集画面を開いた際に、保存済みプロフィール値が各入力項目へ初期表示されるかを検証
    public function test_prefills_profile_form() {
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
        $response->assertSee('/storage/' . $profile->image_path, false);
        $response->assertSee('name="name"', false);
        $response->assertSee('value="初期表示ユーザー名"', false);
        $response->assertSee('name="postal_code"', false);
        $response->assertSee('value="980-0001"', false);
        $response->assertSee('name="address"', false);
        $response->assertSee('value="宮城県仙台市青葉区中央1-1-1"', false);
    }

}
