<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class ItemImagePreview extends Component
{
    use WithFileUploads;

    public $image;

    public function render()
    {
        return view('livewire.item-image-preview');
    }
}
