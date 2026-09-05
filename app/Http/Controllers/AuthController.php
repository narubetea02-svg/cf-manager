<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        return redirect('/dashboard');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง'])->onlyInput('email');
    }

    public function redirectToFacebook()
    {
        if (!config('services.facebook.client_id')) {
            return redirect('/')->with('error', 'Facebook Login ยังไม่ได้ตั้งค่า');
        }

        return Socialite::driver('facebook')
            ->setScopes([
                'public_profile',
                'pages_show_list',
                'pages_read_engagement',
                'pages_manage_metadata',
                'pages_messaging',
            ])
            ->redirect();
    }

    public function handleFacebookCallback(Request $request)
    {
        if (!config('services.facebook.client_id')) {
            return redirect('/')->with('error', 'Facebook Login ยังไม่ได้ตั้งค่า');
        }

        if ($request->error) {
            return redirect('/')->with('error', 'Facebook login ถูกปฏิเสธ');
        }

        try {
            $fbUser = Socialite::driver('facebook')->stateless()->user();
            
            $user = User::updateOrCreate([
                'facebook_id' => $fbUser->id,
            ], [
                'name' => $fbUser->name ?? 'FB User',
                'email' => $fbUser->email ?? ('fb_' . $fbUser->id . '@cfshop.local'),
                'avatar' => $fbUser->avatar ?? '',
                'password' => Hash::make(Str::random(16)),
            ]);

            Auth::login($user);
            return redirect('/dashboard');

        } catch (\Exception $e) {
            return redirect('/')->with('error', 'Facebook login ล้มเหลว');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
