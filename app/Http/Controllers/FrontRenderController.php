<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class FrontRenderController extends Controller
{
    public function authView()
    {
        if (auth()->guard()->check()) {
            return redirect()->route(auth()->guard()->user()->level);
        }
        return Inertia::render('Auth1/Authentication');
    }

    public function layoutView()
    {
        return Inertia::render('Layout');
    }
    public function resetPassword()
    {
        return Inertia::render('Auth1/ResetPassword');
    }
}
