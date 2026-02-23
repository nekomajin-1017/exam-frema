@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endsection

@section('main')
    <div class="profile-header">
        <div class="profile-avatar">
            @if ($user && $user->profile && $user->profile->image_path)
                <img class="profile-avatar-img img-fluid" src="{{ asset('storage/' . $user->profile->image_path) }}" alt="{{ $user->name ?? 'プロフィール画像' }}">
            @endif
        </div>
        <h1 class="section-title section-title--h1 profile-name">{{ $user->name ?? 'ゲスト' }}</h1>
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
