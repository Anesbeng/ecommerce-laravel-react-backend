<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Product;
use App\Models\ShippingSetting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SaveOrderController extends Controller
{
    public function saveOrder(Request $request)
    {
        if (!$request->has('cart') || count($request->cart) === 0) {
            return response()->json(['status' => '400', 'message' => 'Order not saved']);
        }

        // Basic validation of the fields we DO trust from the request
        // (shipping details — not prices)
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'mobile'  => 'required|string|max:30',
            'address' => 'required|string|max:255',
            'city'    => 'required|string|max:100',
            'state'   => 'required|string|max:100',
            'cart'    => 'required|array|min:1',
            'cart.*.product_id' => 'required|exists:products,id',
            'cart.*.qty'        => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => '400',
                'message' => 'Order not saved',
                'errors' => $validator->errors(),
            ], 400);
        }

        $order = DB::transaction(function () use ($request) {
            $subtotal = 0;
            $lineItems = [];

            // Recalculate every price from the database — never trust the browser
            foreach ($request->cart as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) $item['qty'];

                $unitPrice = $product->price;
                $lineTotal = $unitPrice * $qty;
                $subtotal += $lineTotal;

                $lineItems[] = [
                    'product_id' => $product->id,
                    'name'       => $product->title,
                    'size'       => $item['size'] ?? null,
                    'unit_price' => $unitPrice,
                    'price'      => $lineTotal,
                    'qty'        => $qty,
                ];
            }

            // Real shipping cost from settings, not from the request
            $shippingSetting = ShippingSetting::first();
            $shipping = $shippingSetting && $shippingSetting->is_free
                ? 0
                : ($shippingSetting->rate ?? 0);

            // No coupon/discount system yet — always 0 until one exists
            $discount = 0;

            $grandTotal = $subtotal + $shipping - $discount;

            $order = new Order();
            $order->name = $request->name;
            $order->email = $request->email;
            $order->mobile = $request->mobile;
            $order->address = $request->address;
            $order->zip = $request->zip ?? '';
            $order->city = $request->city;
            $order->state = $request->state;
            $order->subtotal = $subtotal;
            $order->grand_total = $grandTotal;
            $order->shipping = $shipping;
            $order->discount = $discount;
            $order->status = 'pending';
            $order->payment_status = 'not_paid';
            $order->user_id = $request->user()->id;
            $order->save();

            foreach ($lineItems as $line) {
                $orderitem = new OrderItems();
                $orderitem->order_id   = $order->id;
                $orderitem->product_id = $line['product_id'];
                $orderitem->name       = $line['name'];
                $orderitem->size       = $line['size'];
                $orderitem->price      = $line['price'];
                $orderitem->unit_price = $line['unit_price'];
                $orderitem->qty        = $line['qty'];
                $orderitem->save();
            }

            return $order;
        });

        return response()->json(['status' => '200', 'message' => 'Order saved successfully']);
    }
}