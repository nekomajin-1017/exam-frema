<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    public const NAME_CARD = 'クレジットカード';
    public const NAME_KONBINI = 'コンビニ払い';
    public const TYPE_CARD = 'card';
    public const TYPE_KONBINI = 'konbini';

    protected $fillable = [
        'name',
        'stripe_method_type',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'payment_method_id');
    }
}
