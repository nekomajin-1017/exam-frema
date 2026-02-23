<section class="purchase-section">
    <h2 class="purchase-section-title">
        <label class="purchase-section-label" for="payment-method-select">支払い方法</label>
    </h2>
    <div class="form-group purchase-form-group">
        <select
            class="form-control payment-method-select"
            id="payment-method-select"
            name="payment_method_id"
            wire:model="selectedPaymentMethodId"
            required
        >
            <option value="" disabled hidden>選択してください</option>
            @foreach ($paymentMethods as $method)
                <option value="{{ $method['id'] }}">{{ $method['name'] }}</option>
            @endforeach
        </select>
        @error('payment_method_id')<p class="field-error">{{ $message }}</p>@enderror
    </div>
</section>
