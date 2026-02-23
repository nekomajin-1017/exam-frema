<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'item_id',
        'checkout_session_id',
        'total_price',
        'payment_method_id',
        'shipping_postal_code',
        'shipping_address',
        'shipping_building',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(Payment::class);
    }
}
