<?php

namespace App\Exports;

use App\Models\VirtualAccount;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class VaExport implements FromView
{
    public function view(): View
    {
        return view('exports.va', [
            'vas' => VirtualAccount::all(),
        ]);
    }
}
