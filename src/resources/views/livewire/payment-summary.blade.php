<section class="purchase-summary">
    <div class="purchase-summary-box">
        <div class="purchase-row">
            <span>商品代金</span>
            <strong>￥{{ number_format($itemPrice) }}</strong>
        </div>
        <div class="purchase-row">
            <span>支払い方法</span>
            <strong>{{ $paymentMethodName }}</strong>
        </div>
    </div>
    <div class="purchase-summary-action">
        <button class="button" type="submit">購入する</button>
    </div>
</section>
