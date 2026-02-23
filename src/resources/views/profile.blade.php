@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('main')
    <div class="profile-form form-layout-680">
        <h1 class="form-title">プロフィール設定</h1>
        <form class="profile-edit-form" action="{{ route('mypage.profile.store') }}" method="post" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="form-group">
                <livewire:profile-image-preview :initial-image-url="$profile && $profile->image_path ? asset('storage/' . $profile->image_path) : null" />
                @error('image')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <x-form-field name="name" label="ユーザー名" :value="$profile->display_name ?? $user->name ?? ''" />
            <x-form-field name="postal_code" label="郵便番号" :value="$profile->postal_code ?? ''" />
            <x-form-field name="address" label="住所" :value="$profile->address ?? ''" />
            <x-form-field name="building" label="建物名" :value="$profile->building ?? ''" />
            <button class="button" type="submit">更新する</button>
        </form>
    </div>
@endsection
