<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Payment;
use App\Models\User;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class CheckoutService
{
    public function resolvePaymentSelection() {
        return Payment::orderBy('id')->get();
    }

    public function findPaymentMethod(int $paymentMethodId) {
        return Payment::find($paymentMethodId);
    }

    public function createSession(Item $item, int $buyerId, Payment $paymentMethod) {
        Stripe::setApiKey((string) config('services.stripe.secret'));
        $cancelUrl = $paymentMethod->stripe_method_type === Payment::TYPE_KONBINI
            ? route('purchase.cancel', ['item_id' => $item->id])
            : route('purchase.cancel');

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
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'item_id' => $item->id,
                'buyer_id' => $buyerId,
                'payment_method_id' => $paymentMethod->id,
            ],
        ]);
    }

    public function completeOrderBySessionId(
        string $sessionId,
        OrderService $orders,
        ProfileService $profiles,
        bool $forceCreateForKonbini = false
    ) {
        $stripeSecret = (string) config('services.stripe.secret');
        if ($stripeSecret === '') {
            return;
        }

        Stripe::setApiKey($stripeSecret);
        $session = StripeSession::retrieve($sessionId);
        if (! $this->shouldCreateOrderForSession($session, $forceCreateForKonbini)) {
            return;
        }

        $buyerId = $this->resolveBuyerId($session);
        $itemId = (int) ($session->metadata['item_id'] ?? 0);
        if (! $buyerId || ! $itemId) {
            return;
        }

        $item = Item::find($itemId);
        $buyer = $buyerId ? User::with('profile')->find($buyerId) : null;
        $profile = $buyer ? $buyer->profile : null;
        if (! $item || ! $profile || ! $profiles->hasShippingAddress($profile)) {
            return;
        }

        $orders->createOrder($sessionId, $buyerId, $item, $this->resolvePaymentMethodId($session), $profile);
    }

    private function shouldCreateOrderForSession(StripeSession $session, bool $forceCreateForKonbini = false) {
        if ($forceCreateForKonbini && $this->isKonbiniSession($session)) {
            return true;
        }

        if (($session->payment_status ?? null) === 'paid') {
            return true;
        }

        if (! $this->isKonbiniSession($session)) {
            return false;
        }

        return ($session->status ?? null) === 'complete';
    }

    public function isSessionPaid(string $sessionId) {
        $stripeSecret = (string) config('services.stripe.secret');
        if ($stripeSecret === '') {
            return false;
        }

        Stripe::setApiKey($stripeSecret);
        $session = StripeSession::retrieve($sessionId);

        return ($session->payment_status ?? null) === 'paid';
    }

    private function isKonbiniSession(StripeSession $session) {
        $paymentMethodId = (int) ($session->metadata['payment_method_id'] ?? 0);
        if ($paymentMethodId) {
            return Payment::whereKey($paymentMethodId)->value('stripe_method_type') === Payment::TYPE_KONBINI;
        }

        $paymentMethodTypes = (array) ($session->payment_method_types ?? []);

        return in_array(Payment::TYPE_KONBINI, $paymentMethodTypes, true);
    }

    private function resolveBuyerId(StripeSession $session) {
        $buyerId = (int) ($session->metadata['buyer_id'] ?? $session->client_reference_id ?? 0);
        if ($buyerId) {
            return $buyerId;
        }

        if (! empty($session->customer_details?->email)) {
            return (int) (User::where('email', $session->customer_details->email)->value('id') ?? 0);
        }

        return 0;
    }

    private function resolvePaymentMethodId(StripeSession $session) {
        $paymentMethodId = (int) ($session->metadata['payment_method_id'] ?? 0);
        if ($paymentMethodId) {
            return $paymentMethodId;
        }

        return (int) (Payment::where('stripe_method_type', Payment::TYPE_CARD)->value('id') ?? 0);
    }
}
