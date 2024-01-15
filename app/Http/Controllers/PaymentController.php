<?php

namespace App\Http\Controllers;

use App\Helpers\BcaHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Payment;
use App\Models\VirtualAccount;

class PaymentController extends Controller
{
    public function index()
    {
        if (!request('status')) {
            return redirect()
                ->route('payments.index', [ 'status' => '00' ]);
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
            ->orWhere('channelCode', '6010')
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
        $vaOptions = VirtualAccount::all()
            ->pluck('name_number', 'id')
            ->prepend('Pilih nomor VA atau nama siswa', '');
        $vas = VirtualAccount::get();
        return view('payments.form', compact('vaOptions', 'vas'));
    }

    public function store(Request $request)
    {
        try {
            // Validations
            $request->validate([
                'va_id' => 'required|exists:virtual_accounts,id',
                'total_payment' => 'required',
                'payment_method' => 'required',
            ]);

            $va = VirtualAccount::find(request('va_id'));

            if (!$va) {
                throw new \Exception('VA tidak ditemukan');
            }

            $totalPayment = request('total_payment');
            $paymentIsLTEOutstanding = $totalPayment <= $va->outstanding;

            if (!$paymentIsLTEOutstanding) {
                throw new \Exception('Jumlah yang dibayarkan lebih besar dari tagihan');
            }

            $paymentData = [];
            
            // Upload file when available
            if ($request->hasFile('proof')) {
                $transferProof = $request->file('proof');
                $path = 'va/'.request('va_id');
                $fileName = $transferProof->getClientOriginalName();
                $transferProof->move($path, $fileName);
                $paymentData['paymentProof'] = $path . '/' . $fileName;
            }

            // Add payment record
            $paymentMethod = request('payment_method');
            $time = time();
            $trxId = $paymentMethod . $time;
            $paymentData = array_merge($paymentData, [
                'partnerServiceId' => '1',
                'customerNo' => $va->number,
                'virtualAccountNumber' => $va->number,
                'virtualAccountName' => $va->user->name,
                'channelCode' => '6010',
                'paidAmount' => json_encode([
                    'value' => $totalPayment,
                    'currency' => 'IDR',
                ]),
                'trxId' => $trxId,
                'paymentRequestId' => $trxId,
                'externalId' => $trxId,
                'paymentFlagStatus' => '00',
                'paymentTypee' => $paymentMethod,
                'accNumberSource' => request('source_account_number'),
                'accNameSource' => request('source_account_name'),
            ]);
            Payment::create($paymentData);

            // Update outstanding bill
            $va->update([
                'outstanding' => $va->outstanding - $totalPayment,
                'description' => BCAHelper::substractBillComponents(
                    $va,
                    $totalPayment
                ),
            ]);

            return redirect()
                ->route('payments.index')
                ->with('success', 'Berhasil melakukan pembayaran');
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
            return redirect()
                ->route('va.index')
                ->with(
                    'success',
                    'Berhasil mengubah Virtual Account: ' . $request->input('number')
                );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(VirtualAccount $va)
    {
        try {
            $va->delete();
            return redirect()
                ->route('va.index')
                ->with(
                    'success',
                    'Berhasil menghapus Virtual Account: ' . $va->number
                );
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
