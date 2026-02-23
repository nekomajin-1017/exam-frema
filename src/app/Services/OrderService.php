<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Item;
use App\Models\Profile;

class OrderService
{
    public function orderExistsForItem(int $itemId): bool
    {
        return Order::where('item_id', $itemId)->exists();
    }

    public function orderExistsForSession(string $sessionId): bool
    {
        return Order::where('checkout_session_id', $sessionId)->exists();
    }

    public function createOrder(
        string $sessionId,
        int $buyerId,
        Item $item,
        int $paymentMethodId,
        Profile $profile
    ): void {
        Order::create([
            'buyer_id' => $buyerId,
            'item_id' => $item->id,
            'checkout_session_id' => $sessionId,
            'total_price' => $item->price,
            'payment_method_id' => $paymentMethodId,
            'shipping_postal_code' => $profile->postal_code,
            'shipping_address' => $profile->address,
            'shipping_building' => $profile->building,
        ]);

        $item->update(['is_sold' => true]);
    }
}
