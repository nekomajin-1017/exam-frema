<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Profile;

class OrderService
{
    public function orderExistsForItem(int $itemId) {
        return Order::where('item_id', $itemId)->exists();
    }

    public function orderExistsForSession(string $sessionId) {
        return Order::where('checkout_session_id', $sessionId)->exists();
    }

    public function createOrder(
        string $sessionId,
        int $buyerId,
        Item $item,
        int $paymentMethodId,
        Profile $profile
    ) {
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

    public function cancelPendingKonbiniOrderForItem(int $buyerId, int $itemId, CheckoutService $checkout) {
        $order = Order::where('buyer_id', $buyerId)
            ->where('item_id', $itemId)
            ->whereHas('paymentMethod', function ($query) {
                $query->where('stripe_method_type', Payment::TYPE_KONBINI);
            })
            ->latest('id')
            ->first();

        if (!$order) {
            return;
        }

        $sessionId = (string) ($order->checkout_session_id ?? '');
        if ($sessionId !== '' && $checkout->isSessionPaid($sessionId)) {
            return;
        }

        $order->delete();

        Item::whereKey($itemId)->update(['is_sold' => false]);
    }
}
