<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileImagePreview extends Component
{
    use WithFileUploads;

    public $image;
    public ?string $initialImageUrl = null;

    public function mount(?string $initialImageUrl = null) {
        $this->initialImageUrl = $initialImageUrl;
    }

    public function render()
    {
        return view('livewire.profile-image-preview');
    }
}
