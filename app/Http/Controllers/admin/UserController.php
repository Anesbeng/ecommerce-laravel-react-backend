<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 200,
            'data'   => User::latest()->get()
        ]);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json(['status' => 200, 'data' => $user]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role'  => 'required|in:customer,admin',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 400, 'errors' => $validator->errors()]);
        }
        $user->name  = $request->name;
        $user->email = $request->email;
        $user->role  = $request->role;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->save();
        return response()->json(['status' => 200, 'message' => 'User updated']);
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['status' => 200, 'message' => 'User deleted']);
    }
}