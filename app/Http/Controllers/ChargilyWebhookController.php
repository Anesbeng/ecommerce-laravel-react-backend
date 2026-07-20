<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Mail\OrderConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ChargilyWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secretKey = env('CHARGILY_SECRET_KEY');

        // 1. Verify the signature — this proves the request really came
        // from Chargily and wasn't tampered with in transit.
        $signature = $request->header('signature');
        $payload = $request->getContent(); // raw request body, same as php://input

        if (!$signature) {
            // No signature at all is never legitimate — Chargily always
            // sends one, so this is either a misconfigured test call or
            // an attempted spoof. Ignore it silently either way.
            return response()->json(['message' => 'Missing signature'], 400);
        }

        $computedSignature = hash_hmac('sha256', $payload, $secretKey);

        if (!hash_equals($computedSignature, $signature)) {
            Log::warning('Chargily webhook: signature mismatch, request ignored.');
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // 2. Signature is valid — decode and identify the event
        $event = json_decode($payload);
        $checkout = $event->data ?? null;

        if (!$checkout || !isset($checkout->id)) {
            return response()->json(['message' => 'Malformed payload'], 400);
        }

        $order = Order::where('chargily_checkout_id', $checkout->id)->first();

        if (!$order) {
            // Nothing to do locally, but still acknowledge so Chargily
            // doesn't keep retrying this webhook forever.
            Log::warning("Chargily webhook: no matching order for checkout {$checkout->id}");
            return response()->json(['message' => 'Order not found'], 200);
        }

        // 3. Handle the event
        switch ($event->type) {
            case 'checkout.paid':
                $order->update(['payment_status' => 'paid']);
                Mail::to($order->email)->send(new OrderConfirmationMail($order));
                break;

            case 'checkout.failed':
            case 'checkout.canceled':
            case 'checkout.expired':
                // Stock was already reserved when the order was first
                // placed (in SaveOrderController), so a failed/canceled
                // payment needs to give that stock back — same pattern
                // used in admin/OrderController when an order is cancelled.
                DB::transaction(function () use ($order) {
                    $order = Order::with('orderItems')->lockForUpdate()->find($order->id);
                    foreach ($order->orderItems as $item) {
                        if ($item->size_id) {
                            DB::table('product_sizes')
                                ->where('product_id', $item->product_id)
                                ->where('size_id', $item->size_id)
                                ->increment('qty', $item->qty);
                        }
                    }
                    $order->payment_status = 'not_paid';
                    $order->save();
                });
                break;

            default:
                // Don't silently ignore event types we haven't handled
                // yet — log the real name so we can add proper handling
                // for it instead of guessing.
                Log::info("Chargily webhook: unhandled event type '{$event->type}' for checkout {$checkout->id}");
                break;
        }

        // 4. Acknowledge receipt — Chargily needs this 200 to stop retrying
        return response()->json(['status' => 'received'], 200);
    }
}