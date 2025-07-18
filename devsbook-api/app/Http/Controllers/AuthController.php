<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
    ) {
        $this->middleware('auth:api', [
            'except' => [
                'login',
                'unauthorized',
            ]
        ]);
    }
    // isso deve ir para uma classe de utilidade de erros
    private function json_401(string $message = 'unauthorized access')
    {
        return \response()->json([
            'status' => 'error',
            'message' => $message,
        ], 401);
    }

    // GET api/401 ()
    public function unauthorized()
    {
        return $this->json_401();
    }

    // POST api/auth/login (email, password)
    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        if (!$email || !$password) {
            return $this->json_401('missing attributes');
        }

        $token = $this->authService->authenticate([
            'email' => $email,
            'password' => $password,
        ]);

        if (!$token) return $this->json_401();

        return [
            'status' => 'success',
            'token' => $token,
        ];
    }

    // POST api/auth/logout (header: token)
    public function logout()
    {
        $this->authService->makeLogout();
        return [
            'status' => 'success',
            'message' => 'user disconnected',
        ];
    }

    // POST api/auth/refresh (header: token)
    public function refresh()
    {
        $token = JWTAuth::getToken();
        $newToken = JWTAuth::refresh($token);
        return [
            'status' => 'success',
            'token' => $newToken,
        ];
    }
}
