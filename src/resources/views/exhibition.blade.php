@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/exhibition.css') }}">
@endsection

@section('main')
    <div class="sell-layout">
        <h1 class="section-title section-title--h1">商品の出品</h1>
    <form action="{{ route('sell.store') }}" method="post" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="form-group">
                <livewire:item-image-preview />
                @error('image')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <h2 class="sell-detail-title">商品の詳細</h2>
            <div class="form-group">
                <fieldset class="category-group">
                    <legend class="form-label">カテゴリー</legend>
                @php
                    $selectedCategories = collect(old('category_ids', []))->map(fn ($id) => (string) $id)->all();
                @endphp
                <div class="category-tags">
                    @foreach ($categories as $category)
                        @php
                            $isChecked = in_array((string) $category->id, $selectedCategories, true);
                        @endphp
                        <label class="category-tag">
                            <input class="category-tag-input" id="category-{{ $category->id }}" type="checkbox" name="category_ids[]" value="{{ $category->id }}" {{ $isChecked ? 'checked' : '' }}>
                            <span class="category-tag-label">{{ $category->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('category_ids')<p class="field-error">{{ $message }}</p>@enderror
                @error('category_ids.*')<p class="field-error">{{ $message }}</p>@enderror
                </fieldset>
            </div>
            <div class="form-group">
                <label class="form-label" for="sell-item-condition">商品の状態</label>
                <select class="form-control form-control--select" id="sell-item-condition" name="item_condition" required>
                    <option value="" disabled hidden {{ old('item_condition') ? '' : 'selected' }}>選択してください</option>
                    @foreach ($conditions as $condition)
                        <option value="{{ $condition }}" {{ old('item_condition') === $condition ? 'selected' : '' }}>
                            {{ $condition }}
                        </option>
                    @endforeach
                </select>
                @error('item_condition')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <h2 class="sell-detail-title">商品名と説明</h2>
            <div class="form-group">
                <label class="form-label" for="sell-name">商品名</label>
                <input class="form-control" id="sell-name" type="text" name="name" value="{{ old('name') }}">
                @error('name')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="sell-brand">ブランド名</label>
                <input class="form-control" id="sell-brand" type="text" name="brand" value="{{ old('brand') }}">
                @error('brand')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="sell-description">商品の説明</label>
                <textarea class="form-control form-control--textarea" id="sell-description" name="description">{{ old('description') }}</textarea>
                @error('description')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="sell-price">販売価格</label>
                <div class="price-input">
                    <span class="price-input-currency">￥</span>
                    <input class="form-control price-input-field" id="sell-price" type="number" name="price" value="{{ old('price') }}">
                </div>
                @error('price')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <button class="button" type="submit">出品する</button>
        </form>
    </div>
@endsection
