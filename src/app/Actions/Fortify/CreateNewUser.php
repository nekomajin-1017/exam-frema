<?php

namespace App\Actions\Fortify;

use App\Http\Requests\RegisterRequest;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input) {
        $registerRequest = app(RegisterRequest::class);
        Validator::make(
            $input,
            $registerRequest->rules(),
            $registerRequest->messages()
        )->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        Profile::create([
            'user_id' => $user->id,
            'display_name' => $user->name,
            'postal_code' => null,
            'address' => null,
            'building' => null,
        ]);

        return $user;
    }
}
