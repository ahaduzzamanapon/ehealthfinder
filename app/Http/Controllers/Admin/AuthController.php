<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // Credentials — change these!
    const ADMIN_EMAIL    = 'admin@ehealthfinder.com';
    const ADMIN_PASSWORD = 'admin@123';

    public function showLogin()
    {
        if (session('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $email    = $request->input('email');
        $password = $request->input('password');

        if ($email === self::ADMIN_EMAIL && $password === self::ADMIN_PASSWORD) {
            session(['admin_logged_in' => true, 'admin_email' => $email]);
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['login' => 'Invalid email or password.'])->withInput();
    }

    public function logout()
    {
        session()->forget('admin_logged_in');
        return redirect()->route('admin.login');
    }
}
