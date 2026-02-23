@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/address.css') }}">
@endsection

@section('main')
    <div class="address-page">
        <div class="purchase-card">
            <h1 class="section-title section-title--h1">送付先住所変更</h1>
            <form class="purchase-address-form" action="{{ route('purchase.address.store', $item) }}" method="post" novalidate>
                @csrf
                <x-form-field name="postal_code" label="郵便番号" placeholder="000-0000" :value="$profile->postal_code ?? ''" />
                <x-form-field name="address" label="住所" placeholder="東京都渋谷区神南1-1-1" :value="$profile->address ?? ''" />
                <x-form-field name="building" label="建物名" placeholder="COACHTECHビル 101" :value="$profile->building ?? ''" />
                <button class="button" type="submit">更新する</button>
            </form>
        </div>
    </div>
@endsection
