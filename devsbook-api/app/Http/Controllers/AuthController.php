<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function create(Request $request)
    {
        // POST api/user (name, email, password, birthdate)

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $birthdate = $request->input('birthdate');

        if (!$name || !$email || !$password || !$birthdate) {
            return [
                'status' => 'error',
                'message' => 'invalid attributes',
            ];
        }

        $passwordEncripted = \password_hash($password, PASSWORD_BCRYPT);

        $emailIsValid = \filter_var($email, \FILTER_VALIDATE_EMAIL);
        if (!$emailIsValid) {
            return [
                'status' => 'error',
                'message' => 'invalid e-mail',
            ];
        }

        $birthdateIsValid = \strtotime($birthdate);
        if (!$birthdateIsValid) {
            return [
                'status' => 'error',
                'message' => 'invalid date',
            ];
        }

        $token = Auth::attempt([
            'email' => $email,
            'password' => $passwordEncripted,
        ]);

        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = $passwordEncripted;
        $user->birthdate = $birthdate;
        $user->token = $token;
        $user->save();

        return [
            'status' => 'success',
            'message' => 'created user',
        ];
    }
}
