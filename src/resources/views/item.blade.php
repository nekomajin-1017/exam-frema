@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/item.css') }}">
@endsection

@section('main')
    <div class="item-layout">
        <div class="item-image">
            @if ($item->image_path)
                <img class="item-image-img img-fluid" src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
            @endif
            @if ($item->is_sold)
                <p class="badge sold-badge">Sold</p>
            @endif
        </div>
        <div class="item-meta">
            <h1 class="section-title section-title--h1">{{ $item->name }}</h1>
            <p class="item-brand">ブランド名 {{ $item->brand ?? 'なし' }}</p>
            <div class="item-price">￥{{ number_format($item->price) }}<span class="item-price-tax">(税込)</span></div>
            <div class="item-stats">
                @auth
                    <form action="{{ route('item.favorite', $item) }}" method="post">
                        @csrf
                        <button class="item-stat item-stat-button" type="submit">
                            <img class="item-stat-icon img-fluid" src="{{ asset($isFavorited ? 'img/heartLogoPink.png' : 'img/heartLogoDefault.png') }}" alt="いいね">
                            <span class="item-stat-count">{{ $item->favorites_count }}</span>
                        </button>
                    </form>
                @else
                    <span class="item-stat">
                        <img class="item-stat-icon img-fluid" src="{{ asset('img/heartLogoDefault.png') }}" alt="いいね">
                        <span class="item-stat-count">{{ $item->favorites_count }}</span>
                    </span>
                @endauth
                <a class="item-stat link-reset" href="#comments">
                    <img class="item-stat-icon img-fluid" src="{{ asset('img/fukidashi.png') }}" alt="コメント">
                    <span class="item-stat-count">{{ $item->comments_count }}</span>
                </a>
            </div>
            <div class="item-actions">
                <form class="item-purchase-form" action="{{ route('purchase.show', $item) }}" method="get">
                    <button class="button {{ $item->is_sold ? 'is-disabled' : '' }}" type="submit" {{ $item->is_sold ? 'disabled' : '' }}>
                        購入手続きへ
                    </button>
                </form>
            </div>

            <section class="item-section" id="comments">
                <h2 class="section-title section-title--h2">商品説明</h2>
                <p>{{ $item->description }}</p>
            </section>

            <section class="item-section">
                <h2 class="section-title section-title--h2">商品情報</h2>
                <div class="item-info-row">
                    <span class="item-info-label">カテゴリー</span>
                    <div class="item-info-value item-category-tags">
                        @foreach ($item->categories as $category)
                            <span class="item-category-tag">{{ $category->name }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="item-info-row">
                    <span class="item-info-label">商品の状態</span>
                    <div class="item-info-value">
                        {{ $item->item_condition }}
                    </div>
                </div>
                <div class="item-info-row">
                    <span class="item-info-label">出品者</span>
                    <div class="item-info-value">
                        {{ $item->user->name }}
                    </div>
                </div>
            </section>

            <section class="item-section">
                <h2 class="section-title section-title--h2 comment-title">コメント({{ $item->comments_count }})</h2>
                <div class="comment-list">
                    @forelse ($item->comments as $comment)
                        @php
                            $profileImage = $comment->user?->profile?->image_path ?? null;
                            $commentImageUrl = $profileImage ? asset('storage/' . $profileImage) : null;
                        @endphp
                        <div class="comment-item">
                            <div class="comment-user">
                                @if ($commentImageUrl)
                                    <img class="comment-avatar img-fluid" src="{{ $commentImageUrl }}" alt="{{ $comment->user->name }}">
                                @else
                                    <span class="comment-avatar comment-avatar--placeholder"></span>
                                @endif
                                <span class="comment-user-name">{{ $comment->user->name }}</span>
                            </div>
                            <p class="comment-body">{{ $comment->comment }}</p>
                        </div>
                    @empty
                        <p class="comment-empty">コメントはまだありません。</p>
                    @endforelse
                </div>
                <form class="comment-form" action="{{ route('item.comment', $item) }}" method="post" novalidate>
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="item-comment">商品へのコメント</label>
                        <textarea id="item-comment" class="form-control form-control--textarea" name="comment">{{ old('comment') }}</textarea>
                        @error('comment')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <button class="button" type="submit">コメントを送信する</button>
                </form>
            </section>
        </div>
    </div>
@endsection
