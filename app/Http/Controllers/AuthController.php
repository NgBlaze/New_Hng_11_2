<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends \Illuminate\Routing\Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string',
            'phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'Bad request',
                'message' => 'Registration unsuccessful',
                'statusCode' => 400,
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => Hash::make($request->password)]
        ));

        $organisation = Organisation::create([
            'name' => $request->firstName . "'s Organisation",
            'description' => $request->description ?? '',
        ]);

        $user->organisations()->attach($organisation->orgId);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful',
            // 'data' => [
            //     'accessToken' => $token,
            //     'user' => $user,
            // ]
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'Bad request',
                'message' => 'Authentication failed',
                'statusCode' => 401,
                'errors' => $validator->errors(),
            ], 401);
        }

        if (!$token = JWTAuth::attempt($validator->validated())) {
            return response()->json([
                'status' => 'Bad request',
                'message' => 'Authentication failed',
                'statusCode' => 401,
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'accessToken' => $token,
                'user' => auth()->user(),
            ]
        ], 200);
    }
}
