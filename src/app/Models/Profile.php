<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_path',
        'display_name',
        'postal_code',
        'address',
        'building',
    ];

    public function getImageUrlAttribute() {
        if (! filled($this->image_path)) {
            return null;
        }

        return Storage::url($this->image_path);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
