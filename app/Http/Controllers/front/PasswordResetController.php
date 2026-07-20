<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function forgot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 400, 'errors' => $validator->errors()]);
        }

        $user = User::where('email', $request->email)->first();

        // Always return the same success response whether or not the
        // email exists — this prevents someone from using this endpoint
        // to check which emails are registered on the site.
        if (!$user) {
            return response()->json(['status' => 200, 'message' => 'If that email exists, a reset link has been sent.']);
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $resetUrl = env('FRONTEND_URL', 'http://localhost:5173') . "/reset-password?token={$token}&email=" . urlencode($request->email);

        Mail::to($request->email)->send(new PasswordResetMail($resetUrl));

        return response()->json(['status' => 200, 'message' => 'If that email exists, a reset link has been sent.']);
    }

    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'token'    => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 400, 'errors' => $validator->errors()]);
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return response()->json(['status' => 400, 'message' => 'Invalid or expired reset link.']);
        }

        // Tokens older than 60 minutes are considered expired
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['status' => 400, 'message' => 'This reset link has expired. Please request a new one.']);
        }

        if (!Hash::check($request->token, $record->token)) {
            return response()->json(['status' => 400, 'message' => 'Invalid or expired reset link.']);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['status' => 400, 'message' => 'Invalid or expired reset link.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['status' => 200, 'message' => 'Password reset successfully.']);
    }
}