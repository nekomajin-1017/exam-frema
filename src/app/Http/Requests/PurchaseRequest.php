<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
            'payment_method_id' => ['required', 'integer', 'exists:payments,id'],
            'shipping_address' => ['required', 'in:1'],
        ];
    }

    public function messages() {
        return [
            'payment_method_id.required' => '支払い方法を選択してください',
            'payment_method_id.integer' => '支払い方法を選択してください',
            'payment_method_id.exists' => '支払い方法が不正です',
            'shipping_address.required' => '配送先を入力してください',
            'shipping_address.in' => '配送先を入力してください',
        ];
    }
}
