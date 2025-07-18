<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;


class AuthService {
    public function authenticate(array $data): string | bool
    {
        return \auth('api')->attempt($data);
    }

    public function makeLogout() {
        auth('api')->logout();
    }
}