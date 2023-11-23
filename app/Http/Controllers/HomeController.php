<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\VirtualAccount;

class HomeController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $usersCount = User::whereNull('deleted_at')->count();
        $vaCount = VirtualAccount::count();
        $totalBill = VirtualAccount::where('is_active', 1)->sum('outstanding');

        return view('dashboard.dashboard', compact('usersCount', 'vaCount', 'totalBill'));
    }
}
