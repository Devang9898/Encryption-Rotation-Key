<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Make sure your User model is in App\Models
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password; // For stronger password rules

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'confirmed', // Requires password_confirmation field
                Password::min(8) // Example: Enforce min 8 characters
                    // ->mixedCase() // Optional: Require mixed case
                    // ->numbers()   // Optional: Require numbers
                    // ->symbols(),  // Optional: Require symbols
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        // Create the user
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                // Add any other default fields for your User model here
            ]);

            // Generate token for the new user
            $token = $user->createToken('AppNameApiAuthToken')->accessToken; // Choose a token name

            return response()->json([
                'message' => 'User registered successfully.',
                'user' => $user, // You might want to omit sensitive data here in a real app
                'access_token' => $token,
            ], 201); // Created

        } catch (\Exception $e) {
            // Log the error or handle it appropriately
            report($e); // Log the exception
            return response()->json(['message' => 'Registration failed. Please try again.'], 500); // Internal Server Error
        }
    }

    /**
     * Log the user in and create token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        // Attempt to authenticate the user
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Authentication passed...
            $user = Auth::user(); // Get the authenticated user
            $token = $user->createToken('AppNameApiAuthToken')->accessToken; // Choose the same or different token name

            return response()->json([
                'message' => 'Login successful.',
                'user' => $user, // You might want to omit sensitive data here
                'access_token' => $token,
            ], 200); // OK

        } else {
            // Authentication failed...
            return response()->json(['message' => 'Invalid credentials.'], 401); // Unauthorized
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Get the current token instance
        $token = $request->user()->token();

        // Revoke the token
        if ($token) {
            $token->revoke();
            return response()->json(['message' => 'Successfully logged out.'], 200); // OK
        } else {
            // This case might happen if the token was already invalid or middleware failed
             return response()->json(['message' => 'Could not log out. Token not found or invalid.'], 400); // Bad Request
        }
    }

     /**
     * Get the authenticated User.
     * (Example of a protected route utility)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}