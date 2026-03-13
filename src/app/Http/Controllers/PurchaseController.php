<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
use App\Models\Item;
use App\Models\Payment;
use App\Services\OrderService;
use App\Services\ProfileService;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function showPurchase(Item $item, CheckoutService $checkout, ProfileService $profiles)
    {
        if ($redirect = $this->redirectIfNotPurchasable($item)) {
            return $redirect;
        }

        $profile = Auth::user()->profile;
        $hasShippingAddress = $profiles->hasShippingAddress($profile);
        $paymentMethods = $checkout->resolvePaymentSelection();

        return view('purchase', compact('item', 'profile', 'hasShippingAddress', 'paymentMethods'));
    }

    public function showPurchaseAddress(Item $item)
    {
        if ($redirect = $this->redirectIfNotPurchasable($item)) {
            return $redirect;
        }

        $profile = Auth::user()->profile;

        return view('address', compact('item', 'profile'));
    }

    public function storePurchase(
        PurchaseRequest $request,
        Item $item,
        OrderService $orders,
        CheckoutService $checkout,
        ProfileService $profiles
    ) {
        if ($redirect = $this->redirectIfNotPurchasable($item)) {
            return $redirect;
        }

        $user = Auth::user();
        $profile = $user->profile;
        if (!$profile || !$profiles->hasShippingAddress($profile)) {
            return redirect()->route('purchase.address', $item);
        }

        $paymentMethod = $checkout->findPaymentMethod($request->input('payment_method_id'));
        if (!$paymentMethod) {
            return redirect()->back()->withErrors([
                'payment_method_id' => '支払い方法が不正です',
            ]);
        }

        $session = $checkout->createSession($item, $user->id, $paymentMethod);
        if ($paymentMethod->stripe_method_type === Payment::TYPE_KONBINI) {
            $created = $orders->createOrder($session->id, $user->id, $item, $paymentMethod->id, $profile);
            if (! $created) {
                return redirect()->route('home');
            }
        }

        return redirect($session->url);
    }

    public function handlePurchaseSuccess(
        Request $request,
        OrderService $orders,
        ProfileService $profiles,
        CheckoutService $checkout
    )
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return redirect()->route('home');
        }

        $checkout->completeOrderBySessionId($sessionId, $orders, $profiles, true);

        return redirect()->route('home');
    }

    public function handlePurchaseCancel(Request $request, OrderService $orders, CheckoutService $checkout)
    {
        $itemId = (int) $request->query('item_id', 0);
        if ($itemId > 0 && Auth::check()) {
            $orders->cancelPendingKonbiniOrderForItem((int) Auth::id(), $itemId, $checkout);
        }

        return redirect()->route('home');
    }

    public function storePurchaseAddress(AddressRequest $request, Item $item, ProfileService $profiles)
    {
        $user = Auth::user();

        $profiles->upsert($user, [
            'display_name' => $user->name,
            'postal_code' => $request->input('postal_code'),
            'address' => $request->input('address'),
            'building' => $request->input('building'),
        ]);

        return redirect()->route('purchase.show', $item);
    }

    private function redirectIfNotPurchasable(Item $item)
    {
        if ($item->user_id === Auth::id()) {
            return redirect()->route('home');
        }

        if ($item->is_sold) {
            return redirect()->route('home');
        }

        return null;
    }
}
