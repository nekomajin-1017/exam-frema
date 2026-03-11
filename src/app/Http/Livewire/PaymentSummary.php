<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PaymentSummary extends Component
{
    protected $listeners = ['paymentMethodUpdated' => 'setPaymentMethodName'];
    public string $paymentMethodName = '';
    public int $itemPrice = 0;

    public function mount(int $itemPrice, string $initialPaymentMethodName = '') {
        $this->itemPrice = $itemPrice;
        $this->paymentMethodName = $initialPaymentMethodName;
    }

    public function setPaymentMethodName(string $name) {
        $this->paymentMethodName = $name;
    }

    public function render()
    {
        return view('livewire.payment-summary');
    }
}
