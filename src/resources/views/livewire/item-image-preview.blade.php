<div>
    <label class="form-label" for="sell-image-input">商品画像</label>
    <div class="sell-image-box">
        @if ($image)
            <img class="sell-image-preview img-fluid" src="{{ $image->temporaryUrl() }}" alt="商品画像プレビュー">
        @else
            <label class="sell-image-button" for="sell-image-input">画像を選択する</label>
        @endif
    </div>
    <input class="is-hidden" id="sell-image-input" type="file" name="image" accept=".jpeg,.jpg,.png" wire:model="image">
</div>
