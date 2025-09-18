<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Validator;

class AuthenticationApiController extends Controller
{
    /**
     * Register API
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return apiResponse(false, 'Validation error', $validator->errors(), 422);
        }

        $nameParts = explode(' ', $request->name, 2);

        $user = User::create([
            'name'     => $request->name,
            'first_name' => $nameParts[0],
            'last_name' => $nameParts[1] ?? '',
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->user_id = generateUniqueId('user');

        $user->syncRoles('User');

        $token = $user->createToken('authToken')->plainTextToken; 

        $data = [
            'token' => $token,
            'user'  => $user,
        ];

        return apiResponse(true, 'User registered successfully', $data, 200);
    }

    /**
     * Login API
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return apiResponse(false, 'Validation error', $validator->errors(), 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return apiResponse(false, 'Invalid login credentials', null, 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('authToken')->plainTextToken;

        $data = [
            'token' => $token,
            'user'  => $user
        ];
        return apiResponse(true, 'Login successful', $data, 200);
    }

    /**
     * Logout API
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return apiResponse(true, 'Logged out successfully', null, 200);
    }
}
