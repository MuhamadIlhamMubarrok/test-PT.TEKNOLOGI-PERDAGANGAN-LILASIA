<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $roles = $user->roles->pluck('role_name')->toArray();

        $users = User::with('roles')->get();

        return view('dashboard', ['users' => $users, 'roles' => $roles]);
    }
}
