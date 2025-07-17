<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private $loggedUser;
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->loggedUser = Auth::user();
    }

    public function update(Request $request) {
        // implementar update com for para evitar ifs
    }
}
