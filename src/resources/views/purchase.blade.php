@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('main')
    @php
        $initialPaymentMethodId = old('payment_method_id');
        $initialPaymentMethodName = (string) ($paymentMethods->firstWhere('id', (int) $initialPaymentMethodId)?->name ?? '');
    @endphp
    <div class="purchase-page">
        <form action="{{ route('purchase.store', $item) }}" method="post" novalidate>
            @csrf
            <div class="purchase-layout">
                <div class="purchase-main">
                <div class="purchase-item">
                    <div class="purchase-thumb">
                        @if ($item->image_url)
                            <img class="purchase-thumb-img img-fluid" src="{{ $item->image_url }}" alt="{{ $item->name }}">
                        @endif
                    </div>
                    <div class="purchase-item-meta">
                        <h1 class="section-title section-title--h1 purchase-item-title">{{ $item->name }}</h1>
                        <p class="purchase-item-price">￥{{ number_format($item->price) }}</p>
                    </div>
                </div>

                <div class="purchase-divider"></div>

                <livewire:payment-selector
                    :payment-methods="$paymentMethods"
                    :old-payment-method-id="old('payment_method_id')"
                />

                <div class="purchase-divider"></div>

                <section class="purchase-section">
                    <div class="purchase-section-header">
                        <h2 class="purchase-section-title">配送先</h2>
                        <a class="link-reset purchase-section-link" href="{{ route('purchase.address', $item) }}">変更する</a>
                    </div>
                    <input type="hidden" name="shipping_address" value="{{ $hasShippingAddress ? '1' : '' }}">
                    @if ($hasShippingAddress)
                        <p class="purchase-address">〒{{ $profile->postal_code }}</p>
                        <p class="purchase-address">{{ $profile->address }}</p>
                        <p class="purchase-address">{{ $profile->building }}</p>
                    @else
                        <p class="purchase-address">住所が未登録です。</p>
                    @endif
                    @error('shipping_address')<p class="field-error purchase-address-error">{{ $message }}</p>@enderror
                </section>
            </div>

                <livewire:payment-summary
                    :item-price="$item->price"
                    :initial-payment-method-name="$initialPaymentMethodName"
                />
            </div>
        </form>
    </div>
@endsection
