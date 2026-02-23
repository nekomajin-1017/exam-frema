<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seller = User::where('email', 'seller@example.com')->firstOrFail();
        Profile::create([
            'user_id' => $seller->id,
            'display_name' => 'テストアカウント（出品者）',
            'postal_code' => '123-0001',
            'address' => '東京都港区芝公園1-1-1',
            'building' => '東京タワービル 101',
        ]);

        $buyer = User::where('email', 'buyer@example.com')->firstOrFail();
        Profile::create([
            'user_id' => $buyer->id,
            'display_name' => 'テストアカウント（購入者）',
            'postal_code' => '456-0002',
            'address' => '東京都新宿区新宿1-1-1',
            'building' => 'サンプルマンション 201',
        ]);
    }
}
