@props([
    'items' => collect(),
    'emptyMessage' => '商品がありません。',
])

<div class="card-grid">
    @forelse ($items as $item)
        @php
            $imagePath = $item->image_path ?? null;
            $imageUrl = $imagePath
                ? (\Illuminate\Support\Str::startsWith($imagePath, ['http://', 'https://'])
                    ? $imagePath
                    : asset('storage/' . $imagePath))
                : null;
        @endphp

        <a class="card link-reset" href="{{ route('item.show', $item) }}">
            <div class="card-image">
                @if ($imageUrl)
                    <img class="card-image-img img-fluid" src="{{ $imageUrl }}" alt="{{ $item->name }}">
                @endif
                @if (($item->is_sold ?? false) || (($item->orders_count ?? 0) > 0))
                    <span class="badge">Sold</span>
                @endif
            </div>
            <p class="card-title">{{ $item->name }}</p>
        </a>
    @empty
        <p>{{ $emptyMessage }}</p>
    @endforelse
</div>
