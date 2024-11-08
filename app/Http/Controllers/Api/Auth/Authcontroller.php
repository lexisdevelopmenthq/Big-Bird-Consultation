<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthRequest;
use App\Models\Auth\Otp;
use App\Models\User;
use App\Traits\HttpResponses;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class Authcontroller extends Controller
{
    use HttpResponses;

    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    try {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error([], "Invalid credentials.", 401);
        }


        if (!$user->is_email_verified) {

            return $this->error([], '`Please verify your email before logging in.',  403);
        }

        // Generate and return token
        $token = $user->createToken('access_token')->plainTextToken;

        return $this->success(['user' =>$user,'token' => $token], 'Login successful.', 200);
    } catch (Exception $e) {
        return $this->error(['error' => $e->getMessage()], 'Login failed. Please try again later.',  500);
    }
}

public function changePassword(Request $request)
{
    try {
        // Validate the current and new passwords
        $validatedData = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Get the authenticated user
        $user = auth()->user();

        // Check if the provided current password matches the stored password
        if (!Hash::check($validatedData['current_password'], $user->password)) {
            return $this->error(['error' => 'Current password is incorrect.'], 'Password change failed.', 403);
        }

        // Ensure new password is different from the current password
        if (Hash::check($validatedData['new_password'], $user->password)) {
            return $this->error(['error' => 'New password must be different from the current password.'], 'Password change failed.', 403);
        }

        // Update the password
        $user->update([
            'password' => Hash::make($validatedData['new_password']),
        ]);

        // Return success response
        return $this->success(null, 'Password changed successfully.', 200);

    } catch (ValidationException $e) {
        return $this->error(['error' => $e->errors()], 'Validation error occurred.', 422);

    } catch (Exception $e) {
        return $this->error(['error' => $e->getMessage()], 'Password change failed. Please try again later.', 500);
    }
}


public function logout()
{
    try {
        $user = Auth::user();
        $user->tokens()->delete();
        return $this->success([], 'Logout successful.', 200);
    } catch (Exception $e) {
        return $this->error('Logout failed. Please try again later.', ['error' => $e->getMessage()], 500);
    }
}


public function register(AuthRequest $request)
{
    try {
        $data = null;

        // Wrapping in a transaction
        DB::transaction(function () use ($request, &$data) {
            $validatedData = $request->validated();
            $validatedData['password'] = Hash::make($validatedData['password']);

            // Create the user
            $user = User::create($validatedData);

            // Generate OTP
            $otp = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $data = Otp::create([
                'user_id' => $user->id,
                'otp' => $otp,
                'expires_at' => now()->addMinutes(3),
            ]);

            // Send the OTP email (currently commented out)
            // Mail::to($user->email)->send(new \App\Mail\SendOtpMail($otp));
            // Mail::to($user->email)->send(new WelcomeMail($user));
            // Mail::to($user->email)->send(new OtpVerificationMail($otp, $user));
        });

        // Ensure success message is returned after transaction completes
        return $this->success(['data' => $data], 'Registered successfully. OTP sent to your email.', 201);

    } catch (Exception $e) {


        return $this->error(['error' => $e->getMessage()], 'Registration failed. Please try again later.', 500);
    }
}

public function updateProfile(AuthRequest $request)
{
    try {
        $data = null;

        DB::transaction(function () use ($request, &$data) {
            // Get the authenticated user
            $user = auth()->user();

            // Validate incoming request data
            $validatedData = $request->validated();

            // Update only first_name and last_name fields
            $user->update([
                'first_name' => $validatedData['first_name'],
                'last_name' => $validatedData['last_name'],
            ]);

            $data = $user;
        });

        // Success response
        return $this->success(['data' => $data], 'Profile updated successfully.', 200);

    } catch (ValidationException $e) {
        return $this->error(['error' => $e->errors()], 'Validation error occurred.', 422);

    } catch (Exception $e) {
        return $this->error(['error' => $e->getMessage()], 'Profile update failed. Please try again later.', 500);
    }
}

public function verifyOtp(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'otp' => 'required|digits:4',
    ]);

    try {
        $user = User::where('email', $request->email)->firstOrFail();
        $otp = Otp::where('user_id', $user->id)
                  ->where('otp', $request->otp)
                  ->where('expires_at', '>', now())
                  ->first();

        if (!$otp) {
            return $this->error( [], 'Invalid or expired OTP.', 400);
        }

        // Mark OTP as used or delete
        $otp->delete();

        $user->update([
            'email_verified_at' => now(),
            'is_email_verified' => true,
        ]);

        // Generate and return token
        $token = $user->createToken('access_token')->plainTextToken;

        return $this->success(['user' =>$user,'token' => $token], 'OTP verified successfully.', 200);
    } catch (Exception $e) {

        return $this->error(['error' => $e->getMessage()],'Verification failed. Please try again later.',  500);
    }
}

public function resendOtp(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    try {
        $user = User::where('email', $request->email)->firstOrFail();


        // Check if an OTP already exists and hasn't expired
        $existingOtp = Otp::where('user_id', $user->id)
                           ->where('expires_at', '>', now())
                           ->first();

        if ($existingOtp) {
            return $this->error( [],'An OTP is already valid. Please wait before requesting a new one.',400);
        }

        $otp = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        Otp::create([
            'user_id' => $user->id,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(3),
        ]);

        // Mail::to($user->email)->send(new \App\Mail\SendOtpMail($otp));

        return $this->success(['otp' => $otp], 'OTP has been sent to your email.', 200);
    } catch (Exception $e) {

        return $this->error(['error' => $e->getMessage()], 'Failed to resend OTP. Please try again later.',  500);
    }
}

public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    try {
        $email = $request->email;

        // Retrieve user by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->error( [], 'No user found with this email address.', 404);
        }

        // Generate and save OTP
        $otp = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        Otp::updateOrCreate(
            ['user_id' => $user->id],
            ['otp' => $otp, 'expires_at' => now()->addMinutes(3)]
        );

        // Generate password reset token
        $token = Password::createToken($user);
        $frontendUrl = env('FRONTEND_URL') . '/reset-password';
        $resetLink = $frontendUrl . '?token=' . $token . '&email=' . urlencode($email);

        // Commented out email sending for now
        // Mail::to($email)->send(new SendOtpAndResetLinkMail($otp, $resetLink));

        return $this->success([
            'otp' => $otp,
            'reset_link' => $resetLink
        ], 'OTP and password reset link generated successfully.', 200);

    } catch (\Exception $e) {
        return $this->error( ['error' => $e->getMessage()], 'Forgot password request failed. Please try again later.', 500);
    }
}

public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'new_password' => 'required|string|confirmed',
        'otp' => 'nullable|integer',
        'token' => 'nullable|string',
    ]);

    $email = $request->email;
    $otp = $request->otp;
    $token = $request->token;
    $newPassword = $request->new_password;

    try {
        if ($token) {
            // Validate token and reset password
            $status = Password::reset(
                ['email' => $email, 'token' => $token, 'password' => $newPassword],
                function ($user, $password) {

                    $user->password = Hash::make($password);
                    $user->save();
                }
            );

            if ($status == Password::PASSWORD_RESET) {
                return $this->success([], 'Password reset successfully using token.', 200);
            } else {
                return $this->error([], 'Invalid token.', 422);
            }
        } elseif ($otp) {
            // Validate OTP
            $otpRecord = Otp::where('user_id', User::where('email', $email)->value('id'))
                            ->where('otp', $otp)
                            ->where('expires_at', '>', now())
                            ->first();

            if (!$otpRecord) {
                return $this->error([], 'Invalid or expired OTP.', 422);
            }

            // Reset password using OTP
            $user = User::where('email', $email)->first();
            $user->password = Hash::make($newPassword);
            $user->save();

            return $this->success([], 'Password reset successfully using OTP.', 200);
        } else {
            return $this->error([], 'OTP or token is required.', 422);
        }
    } catch (Exception $e) {
        return $this->error(['error' => $e->getMessage()], 'Password reset failed. Please try again later.', 500);
    }
}

}
