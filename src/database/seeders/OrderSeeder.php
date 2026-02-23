<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Item;
use App\Models\User;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $buyer = User::where('email', 'buyer@example.com')->with('profile')->firstOrFail();
        $item = Item::where('name', 'HDD')->firstOrFail();
        $paymentMethod = Payment::where('name', 'クレジットカード')->firstOrFail();

        Order::create([
            'buyer_id' => $buyer->id,
            'item_id' => $item->id,
            'total_price' => $item->price,
            'payment_method_id' => $paymentMethod->id,
            'shipping_postal_code' => $buyer->profile->postal_code,
            'shipping_address' => $buyer->profile->address,
            'shipping_building' => $buyer->profile->building,
        ]);

        $item->update(['is_sold' => true]);
    }
}
