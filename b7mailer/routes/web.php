<?php

use App\Http\Controllers\Mails\AuthMailController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/email', [AuthMailController::class, 'sendRegisterMail']);