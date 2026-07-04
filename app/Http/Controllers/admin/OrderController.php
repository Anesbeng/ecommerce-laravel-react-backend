<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // GET /api/admin/orders
    public function index()
    {
        $orders = Order::with('orderItems.product:id,image')
            ->latest()
            ->get();

        return response()->json($orders);
    }

    // GET /api/admin/orders/{id}
    public function show($id)
    {
        $order = Order::with('orderItems.product:id,image')
            ->findOrFail($id);

        return response()->json($order);
    }

    // PUT /api/admin/orders/{id}/status
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        DB::transaction(function () use ($request, $id) {
            $order = Order::with('orderItems.product')->lockForUpdate()->findOrFail($id);
            $wasCancelled = $order->status === 'cancelled';
            $newStatus = $request->status;

            // Restore stock if the order is being cancelled for the first time
            if ($newStatus === 'cancelled' && !$wasCancelled) {
                foreach ($order->orderItems as $item) {
                    if ($item->size_id) {
                        DB::table('product_sizes')
                            ->where('product_id', $item->product_id)
                            ->where('size_id', $item->size_id)
                            ->increment('qty', $item->qty);
                    }
                }
            }

            // If an order is un-cancelled back into an active status,
            // take the stock out again so it stays accurate
            if ($wasCancelled && $newStatus !== 'cancelled') {
                foreach ($order->orderItems as $item) {
                    if ($item->size_id) {
                        DB::table('product_sizes')
                            ->where('product_id', $item->product_id)
                            ->where('size_id', $item->size_id)
                            ->decrement('qty', $item->qty);
                    }
                }
            }

            $order->status = $newStatus;
            $order->save();
        });

        return response()->json(['status' => 200, 'message' => 'Order status updated']);
    }
}