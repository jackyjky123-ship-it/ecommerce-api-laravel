<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function Register(RegisterRequest $request): JsonResponse{
        $user = User::create([
            'name'=> $request->name,
            'email'=> $request->email,
            'password'=>$request->password,
            'role'=> 'customer',
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'User create successfully.',
            'token' => $token,
            'token_type'=> 'Bearer',
            'user'=> $user
        ], 201);
    }

    public function Login(LoginRequest $request): JsonResponse{
        $user = User::where('email', $request->email)->first();

        if(!$user || !Hash::check( $request->password, $user->password)){
            throw ValidationException::withMessages([
                'message'=> 'Invalid email or password!'
            ]);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }
    public function me(Request $request):JsonResponse {
    return response()->json([
        $request->user()
    ]);
    }
    public function logout(Request $request){
        $request->user()->currentAccessToken->delete();

        return response()->json('User logout successfully.');
    }
}
