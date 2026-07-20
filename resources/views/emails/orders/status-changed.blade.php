@component('mail::message')
    # Order #{{ $order->id }} update

    Hi {{ $order->name }},

    Your order status has changed to:

    @component('mail::panel')
        {{ ucfirst($order->status) }}
    @endcomponent

    @if ($order->status === 'shipped')
        Your order is on its way! You'll receive it soon at:
        {{ $order->address }}, {{ $order->city }}, {{ $order->state }}
    @elseif ($order->status === 'delivered')
        We hope you enjoy your order! Thanks for shopping with us.
    @elseif ($order->status === 'cancelled')
        This order has been cancelled. If you paid online, any charged amount will be refunded according to our refund
        policy. Any reserved stock has already been released.
    @endif

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
