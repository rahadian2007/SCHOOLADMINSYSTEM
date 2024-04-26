<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Settings;

class SettingsController extends Controller
{
    protected $KEY_COMMISSION_PERCENT = 'commission_percent';

    public function index()
    {
        $commissionPercent = Settings::where('key', $this->KEY_COMMISSION_PERCENT)->first();
        return view('settings.index', compact('commissionPercent'));
    }

    public function update()
    {
        try {
            $data = [
                'key' => $this->KEY_COMMISSION_PERCENT,
                'value' => request($this->KEY_COMMISSION_PERCENT),
            ];
            $settings = Settings::where('key', $this->KEY_COMMISSION_PERCENT)->first();

            if ($settings) {
                $settings->update($data, ['timestamps' => false]);
            } else {
                Settings::insert($data);
            }

            return redirect()->back()->with('message', 'Berhasil memperbarui settings');
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui settings');
        }
    }
}
