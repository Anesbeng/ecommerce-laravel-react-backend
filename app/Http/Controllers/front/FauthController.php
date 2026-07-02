<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;



class FauthController extends Controller
{
    public function register (Request $request){
        $validate = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
        ]);
        if ($validate->fails()) {
            return response()->json(['status' => 400, 'errors' => $validate->errors()]);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);
       return response()->json(['status' => 200, 'message' => 'Registration successful']);
    }
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
           
                $token = $user->createToken('token')->plainTextToken;
                return response()->json([
                    'status' => '200',
                    'message' => 'Login successful',
                    'token' => $token,
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    
                ], 200);
           
        } else {
            return response()->json([
                'status' => '401',
                'message' => 'Invalid email or password',
            ], 401);
        }
    }
        
     
}