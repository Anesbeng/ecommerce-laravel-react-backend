<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShippingSetting;

class ShippingController extends Controller
{
    public function index()
    {
        $setting = ShippingSetting::first();

        if (!$setting) {
            $setting = ShippingSetting::create([
                'rate' => 0,
                'is_free' => false,
            ]);
        }

        return response()->json($setting);
    }

    public function update(Request $request)
    {
        $request->validate([
            'rate' => 'required|numeric|min:0',
            'is_free' => 'boolean',
        ]);

        // firstOrCreate so this can never hit a null row, no matter
        // what state the table is in
        $setting = ShippingSetting::firstOrCreate([]);
        $setting->rate    = $request->rate;
        $setting->is_free = $request->is_free;
        $setting->save();

        return response()->json(['status' => 200, 'message' => 'Shipping updated']);
    }
}