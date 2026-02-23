<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $payment = [
            ['id' => 1, 'name' => Payment::NAME_KONBINI, 'stripe_method_type' => Payment::TYPE_KONBINI],
            ['id' => 2, 'name' => Payment::NAME_CARD, 'stripe_method_type' => Payment::TYPE_CARD],
        ];

        Payment::upsert($payment, ['id'], ['name', 'stripe_method_type']);
    }
}
