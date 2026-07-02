<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 400, 'errors' => $validator->errors()]);
        }
        $user = $request->user();
        $user->name = $request->name;
        $user->save();
        return response()->json(['status' => 200, 'message' => 'Profile updated', 'name' => $user->name]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password'      => 'required',
            'password'              => 'required|min:8|confirmed',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 400, 'errors' => $validator->errors()]);
        }
        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['status' => 400, 'message' => 'Current password is incorrect']);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json(['status' => 200, 'message' => 'Password changed successfully']);
    }
}