<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\ItemConditions;

class ExhibitionRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'description' => ['required', 'string', 'max:255'],
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'item_condition' => ['required', 'string', Rule::in(ItemConditions::ALL)],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'brand' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '商品名を入力してください',
            'description.required' => '商品説明を入力してください',
            'description.max' => '商品説明は255文字以内で入力してください',
            'image.required' => '商品画像をアップロードしてください',
            'image.image' => '商品画像は.jpeg/.jpg/.png形式でアップロードしてください',
            'image.mimes' => '商品画像は.jpeg/.jpg/.png形式でアップロードしてください',
            'image.max' => '商品画像は2MB以内でアップロードしてください',
            'category_ids.required' => '商品のカテゴリーを選択してください',
            'category_ids.array' => '商品のカテゴリーを選択してください',
            'category_ids.min' => '商品のカテゴリーを選択してください',
            'category_ids.*.exists' => '商品のカテゴリーが不正です',
            'item_condition.required' => '商品の状態を選択してください',
            'item_condition.in' => '商品の状態が不正です',
            'price.required' => '商品価格を入力してください',
            'price.integer' => '商品価格は数値で入力してください',
            'price.min' => '商品価格は0円以上で入力してください',
            'price.max' => '商品価格は100,000,000円以下で入力してください',
        ];
    }
}
