<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Item;
use App\Models\User;
use App\Services\OrderService;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(OrderService $orders)
    {
        $buyer = User::where('email', 'buyer@example.com')
            ->has('profile')
            ->with('profile')
            ->firstOrFail();
        $profile = $buyer->profile;
        $item = Item::where('name', 'HDD')->firstOrFail();
        $paymentMethod = Payment::where('name', Payment::NAME_CARD)->firstOrFail();

        $orders->createOrder(
            'seed_order_hdd',
            $buyer->id,
            $item,
            $paymentMethod->id,
            $profile
        );
    }
}
