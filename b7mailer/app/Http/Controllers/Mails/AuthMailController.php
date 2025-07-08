<?php

namespace App\Http\Controllers\Mails;

use App\Http\Controllers\Controller;
use App\Mail\RegisterMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AuthMailController extends Controller
{
    public function sendRegisterMail() {
        $mail = new RegisterMail();
        
        Mail::to('test@gmail.com')->queue($mail);
        return \redirect('/');
    }
}
