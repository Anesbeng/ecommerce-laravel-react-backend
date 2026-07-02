<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShippingSetting;

class ShippingController extends Controller
{
    public function index()
    {
        return response()->json(ShippingSetting::first());
    }

    public function update(Request $request)
    {
        $setting = ShippingSetting::first();
        $setting->rate    = $request->rate;
        $setting->is_free = $request->is_free;
        $setting->save();
        return response()->json(['status' => 200, 'message' => 'Shipping updated']);
    }
}