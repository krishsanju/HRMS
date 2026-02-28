<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetMail;
use App\Models\Employee;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Send a reset link to the given employee.
     *
     * @param  \App\Http\Requests\ForgotPasswordRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        $employee = Employee::where('email', $request->email)->first();

        if (!$employee) {
            // Always return a generic success message to prevent email enumeration
            return response()->json(['message' => 'If an account with that email exists, a password reset link has been sent.'], 200);
        }

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        $token = Str::random(60);
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        // Send email with reset link
        Mail::to($employee->email)->queue(new PasswordResetMail($token, $employee->email));

        return response()->json(['message' => 'If an account with that email exists, a password reset link has been sent.'], 200);
    }

    /**
     * Reset the given employee's password.
     *
     * @param  \App\Http\Requests\ResetPasswordRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$passwordReset) {
            return response()->json(['message' => 'This password reset token is invalid or has expired.'], 400);
        }

        // Check if token has expired (e.g., 60 minutes as per config/auth.php)
        $expires = config('auth.passwords.employees.expire');
        if (Carbon::parse($passwordReset->created_at)->addMinutes($expires)->isBefore(Carbon::now())) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete(); // Delete expired token
            return response()->json(['message' => 'This password reset token is invalid or has expired.'], 400);
        }

        $employee = Employee::where('email', $request->email)->first();

        if (!$employee) {
            return response()->json(['message' => 'We cannot find an employee with that email address.'], 404);
        }

        $employee->password = $request->password; // Mutator will hash it
        $employee->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password has been successfully reset.'], 200);
    }
}