<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
            'name' => ['required', 'string', 'max:20'],
            'postal_code' => ['required', 'regex:/^\\d{3}-\\d{4}$/'],
            'address' => ['required', 'string'],
            'building' => ['nullable', 'string'],
        ];
    }

    public function messages() {
        return [
            'image.image' => 'プロフィール画像は.jpeg/.jpg/.png形式でアップロードしてください',
            'image.mimes' => 'プロフィール画像は.jpeg/.jpg/.png形式でアップロードしてください',
            'image.max' => 'プロフィール画像は2MB以内でアップロードしてください',
            'name.required' => 'ユーザー名を入力してください',
            'name.max' => 'ユーザー名は20文字以内で入力してください',
            'postal_code.required' => '郵便番号を入力してください',
            'postal_code.regex' => '郵便番号はハイフンありの8文字で入力してください',
            'address.required' => '住所を入力してください',
        ];
    }
}
