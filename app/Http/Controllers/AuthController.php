<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(\App\Http\Requests\RegisterRequest $request)
    {
        try {
            $user = \App\Models\User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'image' => 'https://d3djy7pad2souj.cloudfront.net/munity/avatars/avatar' . rand(1, 5) . '_munity_H265P.png',
                'password' => \Illuminate\Support\Facades\Hash::make($request->input('password')),
                'role' => 'user'
            ]);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], 400);
        }

        return response([
            'message' => 'User registered. Logging in ...',
            'cookie' => json_encode([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'image' => $user->image,
                'role' => $user->role,
                'token' => "Bearer " . JWTAuth::attempt($request->only(['name', 'password'])),
                'ttl' => JWTAuth::factory()->getTTL() * 60
            ])
        ], 200);
    }

    public function signIn(\App\Http\Requests\SignInRequest $request)
    {
        try {
            $credentials = $request->only(['name', 'password']);
            if ($token = JWTAuth::attempt($credentials)) {
                $user = JWTAuth::user();
                return response([
                    'message' => 'Signed in',
                    'cookie' => json_encode([
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'image' => $user->image,
                        'role' => $user->role,
                        'token' => "Bearer " . $token,
                        'ttl' => JWTAuth::factory()->getTTL() * 60
                    ])
                ])->withCookie(cookie('user', json_encode([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'image' => $user->image,
                    'role' => $user->role,
                    'token' => "Bearer " . $token
                ]), JWTAuth::factory()->getTTL()));
            }

            return response([
                'message' => 'Incorrect password!'
            ], 400);
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $e) {
            return response([
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function signOut()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response(['message' => 'Successfully signed out'])->withoutCookie('user');
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response(['error' => $e->getMessage()], 401);
        }
    }

    public function refreshToken()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            return response(['token' => $newToken]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response(['error' => $e->getMessage()], 401);
        }
    }
}
