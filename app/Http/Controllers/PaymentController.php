<?php

namespace App\Http\Controllers;

use App\Helpers\BcaHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Payment;
use App\Models\VirtualAccount;

class PaymentController extends Controller
{
    public function __construct()
    {
        BcaHelper::evalAccessToken();
    }

    public function index()
    {
        if (!request('status')) {
            return redirect()->route('payments.index', [ 'status' => '00' ]);
        }

        $query = Payment::query();

        $query->when(!!request('status'), function($q) {
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

        $payments = $query
            ->where('channelCode', '6011')
            ->orderByDesc('created_at')
            ->paginate(10);

        $data = compact('payments');

        return view('payments.index', $data);
    }

    public function show(VirtualAccount $va)
    {
        $payments = Payment::where('virtualAccountNumber', $va->number)->get();
        return view('va.detail', compact('va', 'payments'));
    }

    public function create()
    {
        $va = new VirtualAccount();
        $userOptions = User::pluck('name', 'id');
        return view('va.form', compact('userOptions', 'va'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required',
                'number' => 'required|unique:virtual_accounts|max:28',
                'outstanding' => 'required',
                'is_active' => 'required',
            ]);
            VirtualAccount::create($this->getPayloadDataFromRequest($request));
            return redirect()->route('va.index')->with('success', 'Berhasil menambah Virtual Account: ' . $request->input('number'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit(VirtualAccount $va)
    {
        $userOptions = User::pluck('name', 'id');
        return view('va.form', compact('userOptions', 'va'));
    }

    public function update(Request $request, VirtualAccount $va)
    {
        try {
            $request->validate([
                'user_id' => 'required',
                'number' => 'required|max:28',
                'outstanding' => 'required',
                'is_active' => 'required',
            ]);
            $va->update($this->getPayloadDataFromRequest($request));
            return redirect()->route('va.index')->with('success', 'Berhasil mengubah Virtual Account: ' . $request->input('number'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(VirtualAccount $va)
    {
        try {
            $va->delete();
            return redirect()->route('va.index')->with('success', 'Berhasil menghapus Virtual Account: ' . $va->number);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
