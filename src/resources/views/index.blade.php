@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('main')
    <div class="index-page">
        @php
            $recommendParams = $keyword ? ['keyword' => $keyword] : [];
            $mylistParams = ['tab' => 'mylist'] + ($keyword ? ['keyword' => $keyword] : []);
        @endphp
        <nav class="tabs">
            <a class="tab-link link-reset {{ ($tab ?? '') !== 'mylist' ? 'is-active' : '' }}" href="{{ route('home', $recommendParams) }}">おすすめ</a>
            <a class="tab-link link-reset {{ ($tab ?? '') === 'mylist' ? 'is-active' : '' }}" href="{{ route('home', $mylistParams) }}">マイリスト</a>
        </nav>

        <x-item-grid :items="$items" empty-message="商品がありません。" />
    </div>
@endsection
