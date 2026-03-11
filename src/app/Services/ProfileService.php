<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function upsert(User $user, array $data, ?UploadedFile $image = null) {
        $profile = Profile::where('user_id', $user->id)->first();

        if ($image) {
            $data['image_path'] = $image->store('profiles', 'public');
            $oldImagePath = $profile?->image_path;
            if ($oldImagePath && $oldImagePath !== $data['image_path']) {
                Storage::disk('public')->delete($oldImagePath);
            }
        }

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );
    }

    public function hasShippingAddress(?Profile $profile) {
        return (bool) (
            $profile
            && filled($profile->postal_code)
            && filled($profile->address)
        );
    }
}
