<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Payment;
use App\Models\VirtualAccount;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $usersCount = User::whereNull('deleted_at')
            ->where('menuroles', 'not like', '%admin%')
            ->count();
        $vaCount = VirtualAccount::count();
        $totalBill = VirtualAccount::where('is_active', 1)->sum('outstanding');
        
        $paymentQuery = Payment::query();

        $paymentQuery->when(!!request('status'), function($q) {
            return $q->where('paymentFlagStatus', request('status'));
        })->when(request('period') === 'today', function($q) {
            return $q->whereDate('created_at', DB::raw('CURDATE()'));
        })->when(request('period') === 'last-7-days', function($q) {
            return $q
                ->whereDate('created_at', '>=', Carbon::now()->subDays(7))
                ->whereDate('created_at', '<=', Carbon::now());
        })->when(request('period') === 'last-30-days', function($q) {
            return $q
                ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
                ->whereDate('created_at', '<=', Carbon::now());
        });

        $payments = $paymentQuery
            ->where('channelCode', '6011')
            ->get();
        
        $totalSuccessPayment = 0;
        foreach ($payments as $p) {
            $paidAmount = json_decode($p->paidAmount);
            $totalSuccessPayment += $paidAmount->value;
        }

        return view('dashboard.dashboard', compact(
            'usersCount', 'vaCount', 'totalBill', 'totalSuccessPayment'
        ));
    }
}
