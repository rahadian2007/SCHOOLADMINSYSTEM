<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payment;
use App\Models\VirtualAccount;

class VirtualAccountController extends Controller
{
    public function index()
    {
        $filter = request('q');
        $query = VirtualAccount::query()
            ->when(!!$filter, function ($q) use ($filter) {
                return $q->where('number', 'LIKE', '%'.$filter.'%')
                    ->orWhereHas('user', function ($q2) {
                        return $q2->where('name', 'LIKE', '%'.$filter.'%');
                    });
            });
        $vas = $query->paginate(10);
        $totalBill = $query->where('is_active', 1)->sum('outstanding');
        $vaCount = $query->count();
        $data = compact('vas', 'totalBill', 'vaCount');
        return view('va.index', $data);
    }

    public function show(VirtualAccount $va)
    {
        $payments = Payment::where('virtualAccountNumber', $va->number)
            ->where('channelCode', '6011')
            ->get();
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

    private function getPayloadDataFromRequest(Request $request)
    {
        $data = $request->except('_token', '_method');
        $data['is_active'] = $request->input('is_active') === '1';
        $details = [];
        foreach ($request->get('detail-name') as $index => $name) {
            $details[] = [
                'name' => $name,
                'value' => $request->get('detail-value')[$index],
            ];
        }
        $data['description'] = $details;

        return $data;
    }
}
