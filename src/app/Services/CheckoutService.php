<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Payment;
use App\Models\User;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class CheckoutService
{
    public function resolvePaymentSelection(): array
    {
        $paymentMethods = Payment::orderBy('id')->get();
        $defaultPaymentMethodId = Payment::where('stripe_method_type', Payment::TYPE_KONBINI)->value('id')
            ?? $paymentMethods->first()?->id;

        return [$paymentMethods, $defaultPaymentMethodId];
    }

    public function findPaymentMethod(int $paymentMethodId): ?Payment
    {
        return Payment::find($paymentMethodId);
    }

    public function createSession(Item $item, int $buyerId, Payment $paymentMethod): StripeSession
    {
        Stripe::setApiKey((string) config('services.stripe.secret'));

        return StripeSession::create([
            'mode' => 'payment',
            'payment_method_types' => [$paymentMethod->stripe_method_type],
            'client_reference_id' => (string) $buyerId,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $item->name,
                    ],
                    'unit_amount' => $item->price,
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('purchase.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('purchase.cancel'),
            'metadata' => [
                'item_id' => $item->id,
                'buyer_id' => $buyerId,
                'payment_method_id' => $paymentMethod->id,
            ],
        ]);
    }

    public function completeOrderBySessionId(string $sessionId, OrderService $orders, ProfileService $profiles): void
    {
        $stripeSecret = (string) config('services.stripe.secret');
        if ($stripeSecret === '') {
            return;
        }

        Stripe::setApiKey($stripeSecret);
        $session = StripeSession::retrieve($sessionId);
        if (($session->payment_status ?? null) !== 'paid') {
            return;
        }

        $buyerId = $this->resolveBuyerId($session);
        $itemId = (int) ($session->metadata['item_id'] ?? 0);
        if (!$buyerId || !$itemId || $orders->orderExistsForSession($sessionId)) {
            return;
        }

        $item = Item::find($itemId);
        $buyer = $buyerId ? User::with('profile')->find($buyerId) : null;
        $profile = $buyer ? $buyer->profile : null;
        if (!$item || !$profile || !$profiles->hasShippingAddress($profile)) {
            return;
        }

        $orders->createOrder($sessionId, $buyerId, $item, $this->resolvePaymentMethodId($session), $profile);
    }

    private function resolveBuyerId(StripeSession $session): int
    {
        $buyerId = (int) ($session->metadata['buyer_id'] ?? $session->client_reference_id ?? 0);
        if ($buyerId) {
            return $buyerId;
        }

        if (!empty($session->customer_details?->email)) {
            return (int) (User::where('email', $session->customer_details->email)->value('id') ?? 0);
        }

        return 0;
    }

    private function resolvePaymentMethodId(StripeSession $session): int
    {
        $paymentMethodId = (int) ($session->metadata['payment_method_id'] ?? 0);
        if ($paymentMethodId) {
            return $paymentMethodId;
        }

        return (int) (Payment::where('stripe_method_type', Payment::TYPE_CARD)->value('id') ?? 0);
    }
}
