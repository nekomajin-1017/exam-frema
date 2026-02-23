<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Favorite;
use App\Models\Item;
use App\Models\User;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $buyer = User::where('email', 'buyer@example.com')->firstOrFail();
        $item = Item::where('name', '腕時計')->firstOrFail();

        Favorite::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
        ]);
    }
}
