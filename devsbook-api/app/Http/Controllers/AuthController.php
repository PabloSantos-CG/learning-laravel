<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'login',
                'create',
                'unauthorized',
            ]
        ]);
    }

    private function json_401(string $message = 'unauthorized access')
    {
        return \response()->json([
            'status' => 'error',
            'message' => $message,
        ], 401);
    }

    private function authenticate(array $data): string | bool
    {
        return \auth('api')->attempt($data);
    }

    // GET api/401 ()
    public function unauthorized()
    {
        return $this->json_401();
    }

    // POST api/user (name, email, password, birthdate)
    public function create(Request $request)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $birthdate = $request->input('birthdate');

        if (!$name || !$email || !$password || !$birthdate) {
            return \response()->json([
                'status' => 'error',
                'message' => 'invalid attributes',
            ], 400);
        }

        $userExists = User::where('email', $email)->first();
        if ($userExists) {
            return \response()->json([
                'status' => 'error',
                'message' => 'user exists',
            ], 400);
        }

        $passwordEncripted = \password_hash($password, PASSWORD_BCRYPT);

        $emailIsValid = \filter_var($email, \FILTER_VALIDATE_EMAIL);
        if (!$emailIsValid) {
            return \response()->json([
                'status' => 'error',
                'message' => 'invalid e-mail',
            ], 400);
        }

        $birthdateIsValid = \strtotime($birthdate);
        if (!$birthdateIsValid) {
            return \response()->json([
                'status' => 'error',
                'message' => 'invalid date',
            ], 400);
        }

        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = $passwordEncripted;
        $user->birthdate = $birthdate;
        $user->save();

        $token = $this->authenticate([
            'email' => $email,
            'password' => $password
        ]);

        if (!$token) {
            return \response()->json([
                'status' => 'error',
                'message' => 'authentication failed',
            ], 400);
        }

        return [
            'status' => 'success',
            'message' => 'created user',
            'token' => $token,
        ];
    }

    // POST api/auth/login (email, password)
    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        if (!$email || !$password) {
            return $this->json_401('missing attributes');
        }

        $token = $this->authenticate([
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
        auth('api')->logout();
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
