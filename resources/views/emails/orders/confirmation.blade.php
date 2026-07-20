@component('mail::message')
    # Order Confirmed! 🎉

    Hi {{ $order->name }},

    Thanks for shopping with us! We've received your payment and your order is now being prepared.

    @component('mail::panel')
        **Order #{{ $order->id }}**
    @endcomponent

    ## Order Summary

    @component('mail::table')
        | Item | Size | Qty | Price |
        | :--- | :--: | :-: | ----: |
        @foreach ($order->orderItems as $item)
            | {{ $item->name }} | {{ $item->size }} | {{ $item->qty }} | {{ number_format($item->price, 2) }} DZD |
        @endforeach
    @endcomponent

    <table style="width:100%; margin-top: 10px;">
        <tr>
            <td style="text-align: right; padding: 4px 0;">Subtotal:</td>
            <td style="text-align: right; padding: 4px 0; width: 120px;">{{ number_format($order->subtotal, 2) }} DZD</td>
        </tr>
        <tr>
            <td style="text-align: right; padding: 4px 0;">Shipping:</td>
            <td style="text-align: right; padding: 4px 0;">{{ number_format($order->shipping, 2) }} DZD</td>
        </tr>
        <tr>
            <td style="text-align: right; padding: 8px 0; font-weight: bold; border-top: 1px solid #e8e5ef;">Total:</td>
            <td style="text-align: right; padding: 8px 0; font-weight: bold; border-top: 1px solid #e8e5ef;">
                {{ number_format($order->grand_total, 2) }} DZD</td>
        </tr>
    </table>

    ---

    ## Shipping Address

    {{ $order->address }}
    {{ $order->city }}, {{ $order->state }}

    We'll send you another email as soon as your order ships.

    Thanks for choosing us,<br>
    {{ config('app.name') }}
@endcomponent
