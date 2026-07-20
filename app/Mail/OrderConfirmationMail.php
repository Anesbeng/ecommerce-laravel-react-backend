<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        // order_items already stores name/size/price as a snapshot from
        // checkout time, so we just need the items themselves — no need
        // to join the live product table (which could have changed since).
        $this->order = $order->load('orderItems');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Pure Wear order #{$this->order->id} is confirmed",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.confirmation',
            with: ['order' => $this->order],
        );
    }
}