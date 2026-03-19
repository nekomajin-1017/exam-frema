<?php

namespace Tests\Concerns;

use App\Models\Item;
use App\Models\Payment;
use App\Models\Profile;
use App\Models\User;
use App\Support\ItemConditions;
use Illuminate\Support\Facades\Hash;

trait CreatesTestModels
{
    protected function createItem(int $userId, string $name, array $attributes = []) {
        return Item::create(array_merge([
            'user_id' => $userId,
            'name' => $name,
            'brand' => 'テストブランド',
            'description' => 'テスト商品説明',
            'price' => 1000,
            'item_condition' => ItemConditions::ALL[0],
            'is_sold' => false,
            'image_path' => 'dummy.jpg',
        ], $attributes));
    }

    protected function createUser(string $name) {
        return User::create([
            'name' => $name,
            'email' => $name . '@example.com',
            'password' => Hash::make('Coachtech777'),
        ]);
    }

    protected function createVerifiedUser(string $name) {
        $user = $this->createUser($name);
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    protected function createProfile(
        int $userId,
        ?string $imagePath = null,
        string $displayName = '表示名',
        ?string $postalCode = '650-0001',
        ?string $address = '兵庫県神戸市中央区加納町4-4-4',
        ?string $building = '神戸タワー12F'
    ) {
        return Profile::create([
            'user_id' => $userId,
            'image_path' => $imagePath,
            'display_name' => $displayName,
            'postal_code' => $postalCode,
            'address' => $address,
            'building' => $building,
        ]);
    }

    protected function createPayment(
        string $name = Payment::NAME_CARD,
        string $type = Payment::TYPE_CARD
    ) {
        return Payment::create([
            'name' => $name,
            'stripe_method_type' => $type,
        ]);
    }
}
