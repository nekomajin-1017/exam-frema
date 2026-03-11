<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PaymentSelector extends Component
{
    public $paymentMethods = [];
    public $selectedPaymentMethodId = '';

    public function mount($paymentMethods, $oldPaymentMethodId = null) {
        $this->paymentMethods = collect($paymentMethods)
            ->map(function ($method) {
                return [
                    'id' => (int) $method->id,
                    'name' => (string) $method->name,
                ];
            })
            ->values()
            ->all();

        $this->selectedPaymentMethodId = filled($oldPaymentMethodId) ? (string) $oldPaymentMethodId : '';
    }

    public function updatedSelectedPaymentMethodId() {
        $this->emit('paymentMethodUpdated', $this->selectedPaymentMethodName);
    }

    public function getSelectedPaymentMethodNameProperty() {
        if ($this->selectedPaymentMethodId === '') {
            return '';
        }

        $selected = collect($this->paymentMethods)->firstWhere('id', (int) $this->selectedPaymentMethodId);

        return $selected ? (string) $selected['name'] : '';
    }

    public function render()
    {
        return view('livewire.payment-selector');
    }
}
