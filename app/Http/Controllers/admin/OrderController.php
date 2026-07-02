<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

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
        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json(['status' => 200, 'message' => 'Order status updated']);
    }
}