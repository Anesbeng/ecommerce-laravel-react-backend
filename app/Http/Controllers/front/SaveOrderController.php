<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItems;
use Illuminate\Support\Facades\Validator;

class SaveOrderController extends Controller
{
    public function saveOrder(Request $request)
    {
       if($request->has('cart') && count($request->cart) > 0){
        $order = new Order();
        $order->name = $request->name;
        $order->email = $request->email;
        $order->mobile = $request->mobile;
        $order->address = $request->address;
        $order->zip = $request->zip;
        $order->city = $request->city;
        $order->state = $request->state;
        $order->subtotal = $request->subtotal;
        $order->grand_total = $request->grand_total;
        $order->shipping = $request->shipping;
        $order->discount = $request->discount;
        $order->status = $request->status;
        $order->payment_status = $request->payment_status;
        $order->user_id = $request->user()->id;

        $order->save();

foreach($request->cart as $item){

    $orderitem = new OrderItems();

    $orderitem->order_id = $order->id;
   // Laravel reads plain fields:
$orderitem->product_id = $item['product_id'];
$orderitem->name       = $item['name'];
$orderitem->size       = $item['size'];  // already "XL"

    $orderitem->price = $item['price'] * $item['qty'];
    $orderitem->unit_price = $item['price'];
    $orderitem->qty = $item['qty'];

    $orderitem->save();
}
        return response()->json(['status' => '200','message' => 'Order saved successfully']);
       }
       else{
           
        return response()->json(['status' => '400','message' => 'Order not saved']);
       }
           
       }}
       