<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;

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
        return DB::transaction(function () use ($sessionId, $buyerId, $item, $paymentMethodId, $profile) {
            $lockedItem = Item::whereKey($item->id)->lockForUpdate()->first();
            if (! $lockedItem) {
                return false;
            }

            if ($lockedItem->is_sold) {
                return false;
            }

            if ($sessionId !== '' && Order::where('checkout_session_id', $sessionId)->exists()) {
                return false;
            }

            if (Order::where('item_id', $lockedItem->id)->exists()) {
                return false;
            }

            Order::create([
                'buyer_id' => $buyerId,
                'item_id' => $lockedItem->id,
                'checkout_session_id' => $sessionId,
                'total_price' => $lockedItem->price,
                'payment_method_id' => $paymentMethodId,
                'shipping_postal_code' => $profile->postal_code,
                'shipping_address' => $profile->address,
                'shipping_building' => $profile->building,
            ]);

            $lockedItem->update(['is_sold' => true]);

            return true;
        });
    }

    public function cancelPendingKonbiniOrderForItem(int $buyerId, int $itemId, CheckoutService $checkout) {
        DB::transaction(function () use ($buyerId, $itemId, $checkout) {
            $lockedItem = Item::whereKey($itemId)->lockForUpdate()->first();
            if (! $lockedItem) {
                return;
            }

            $order = Order::where('buyer_id', $buyerId)
                ->where('item_id', $itemId)
                ->whereHas('paymentMethod', function ($query) {
                    $query->where('stripe_method_type', Payment::TYPE_KONBINI);
                })
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $order) {
                return;
            }

            $sessionId = (string) ($order->checkout_session_id ?? '');
            if ($sessionId !== '' && $checkout->isSessionPaid($sessionId)) {
                return;
            }

            $order->delete();
            $lockedItem->update(['is_sold' => false]);
        });
    }
}
