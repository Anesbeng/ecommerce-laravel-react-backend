<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class Authcontroller extends Controller
{
     public function authenticate(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
            'status' => '400',    
            'errors' => $validator->errors()], 400);
        }
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = User::find (Auth::user()->id);
            if($user->role == 'admin'){
                $token = $user->createToken('token')->plainTextToken;
                return response()->json([
                    'status' => '200',
                    'message' => 'Login successful',
                    'token' => $token,
                    'id' => $user->id,
                    'name' => $user->name,
                    
                ], 200);
            }else{
                return response()->json([
                    'status' => '401',
                    'message' => 'Unauthorized access. Admins only.',
                ], 401);
            }
        } else {
            return response()->json([
                'status' => '401',
                'message' => 'Invalid email or password',
            ], 401);
        }
        
     }


public function changePassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'current_password' => 'required',
        'password'         => 'required|min:8|confirmed',
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