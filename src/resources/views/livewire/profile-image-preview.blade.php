<div class="profile-image-row">
    <div class="profile-image-display">
        @if ($image)
            <img class="profile-image-display-img img-fluid" src="{{ $image->temporaryUrl() }}" alt="プロフィール画像プレビュー">
        @elseif ($initialImageUrl)
            <img class="profile-image-display-img img-fluid" src="{{ $initialImageUrl }}" alt="プロフィール画像">
        @else
            <img class="profile-image-display-img img-fluid is-hidden" alt="プロフィール画像">
        @endif
    </div>

    <label class="button button-outline profile-image-button" for="profile-image-input">画像を選択する</label>
    <input class="is-hidden" id="profile-image-input" type="file" name="image" accept=".jpeg,.jpg,.png" wire:model="image">
</div>
