<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;

class ForgotPasswordController extends Controller
{
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function handleForgotPassword(Request $request)
    {
        \Log::info('Received request:', $request->all());

        try {
            $request->validate(['email' => 'required|email']);

            // Try the 'parents' broker first
            $status = Password::broker('parents')->sendResetLink($request->only('email'));

            // If the 'parents' broker fails, try the 'health_workers' broker
            if ($status !== Password::RESET_LINK_SENT) {
                $status = Password::broker('health_workers')->sendResetLink($request->only('email'));
            }

            \Log::info('Password reset status:', ['status' => $status]);

            return response()->json([
                'status' => $status === Password::RESET_LINK_SENT,
                'message' => __($status)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in handleForgotPassword:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function handleReset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$%^&*()_+\-=\[\]{}|;:,.<>?]).{8,}$/'
            ],
        ]);

        // Try resetting password with the 'parents' broker
        $status = Password::broker('parents')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ]);

                $user->save();

                event(new PasswordReset($user));
            }
        );

        // If the 'parents' broker fails, try the 'health_workers' broker
        if ($status !== Password::PASSWORD_RESET) {
            $status = Password::broker('health_workers')->reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ]);

                    $user->save();

                    event(new PasswordReset($user));
                }
            );
        }

        return response()->json([
            'status' => $status === Password::PASSWORD_RESET,
            'message' => __($status)
        ]);
    }
}