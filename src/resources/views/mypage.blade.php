@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endsection

@section('main')
    <div class="profile-header">
        <div class="profile-avatar">
            @if ($user->profile?->image_url)
                <img class="profile-avatar-img img-fluid" src="{{ $user->profile->image_url }}" alt="{{ $user->name }}">
            @endif
        </div>
        <h1 class="section-title section-title--h1 profile-name">{{ $user->name }}</h1>
        <div class="profile-actions">
            <a class="button button-outline link-reset" href="{{ route('mypage.profile') }}">プロフィールを編集</a>
        </div>
    </div>

    <nav class="tabs">
        <a class="tab-link link-reset {{ ($page ?? '') !== 'sell' ? 'is-active' : '' }}" href="{{ route('mypage', ['page' => 'buy']) }}">購入した商品</a>
        <a class="tab-link link-reset {{ ($page ?? '') === 'sell' ? 'is-active' : '' }}" href="{{ route('mypage', ['page' => 'sell']) }}">出品した商品</a>
    </nav>

    @php
        $isSellPage = ($page ?? '') === 'sell';
        $itemsForPage = $isSellPage
            ? $sellItems
            : $buyOrders->map->item->filter();
        $emptyMessage = $isSellPage
            ? '出品した商品がありません。'
            : '購入した商品がありません。';
    @endphp

    <x-item-grid :items="$itemsForPage" :empty-message="$emptyMessage" />
@endsection
