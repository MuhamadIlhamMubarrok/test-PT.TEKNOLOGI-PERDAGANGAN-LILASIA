<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        try {
            $rules = [
                'email' => 'required|email',
                'password' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return redirect()->back()->withErrors(['email' => 'Email and password do not match'])->withInput();
            }

            return redirect()->route('dashboard')->with('status', 'Successfully logged in');
        } catch (\Exception $e) {
            Log::error('Failed to login User: ' . $e->getMessage());
            return redirect()->back()->withErrors(['general' => 'Failed to login User: ' . $e->getMessage()])->withInput();
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|confirmed',
            ]);

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ];

            $user = User::create($data);

            Auth::login($user);

            return redirect()->route('dashboard')->with('status', 'Successfully registered and logged in');
        } catch (\Exception $e) {
            Log::error('Failed to register User: ' . $e->getMessage());
            return redirect()->back()->withErrors(['general' => 'Failed to register User: ' . $e->getMessage()])->withInput();
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Successfully logged out');
        } catch (\Exception $e) {
            Log::error('Failed to log out User: ' . $e->getMessage());
            return redirect()->route('dashboard')->withErrors(['general' => 'Failed to log out User: ' . $e->getMessage()]);
        }
    }
}
