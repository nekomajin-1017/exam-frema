<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'テストアカウント(出品者)',
            'email' => 'seller@example.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'テストアカウント(購入者)',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->call([
            ProfileSeeder::class,
            PaymentSeeder::class,
            CategorySeeder::class,
            ItemSeeder::class,
            FavoriteSeeder::class,
            CommentSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
