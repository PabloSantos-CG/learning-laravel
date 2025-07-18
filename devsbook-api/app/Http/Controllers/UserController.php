<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private $loggedUser;

    public function __construct(
        private UserService $userService,
    ) {
        $this->middleware('auth:api', ['except' => ['create']]);
        $this->loggedUser = Auth::user();
    }

    public function create(Request $request, AuthService $authService)
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

        $userExists = $this->userService->getFirstUser('email', $email);

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

        $this->userService->createUser([
            'name' => $name,
            'email' => $email,
            'password' => $passwordEncripted,
            'birthdate' => $birthdate,
        ]);

        // user ficou amarrado ao auth, nao está correto
        $token = $authService->authenticate([
            'email' => $email,
            'password' => $password
        ]);

        if (!$token) {
            return \response()->json([
                'status' => 'error',
                'message' => 'authentication failed',
            ], 400);
        }

        return \response()->json([
            'status' => 'success',
            'message' => 'created user',
            'token' => $token,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        // implementar update com for para evitar ifs
        $attributes = [];
        $attributesLabel = ['name', 'email', 'birthdate'];

        foreach ($attributesLabel as $label) {
            $checkingAttribute = $request->input($label);
            if ($checkingAttribute) $attributes[$label] = $checkingAttribute;
        }
        $result = $this->userService->updateUser($id, $attributes);

        if (!$result) {
            return \response()->json([
                'status' => 'error',
                'message' => 'bad request'
            ], 400);
        }

        return \response()->json([
            'status' => 'success',
            'message' => 'updated user'
        ], 200);
    }
}
