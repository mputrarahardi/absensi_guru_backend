<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    /**
     * Tampilkan form settings jadwal
     */
    public function index()
    {
        $settings = [
            'check_in_time' => Cache::get('check_in_time', '07:00'),
            'check_out_time' => Cache::get('check_out_time', '17:00'),
            'check_in_before_minutes' => Cache::get('check_in_before_minutes', 30),
            'check_in_after_minutes' => Cache::get('check_in_after_minutes', 30),
        ];

        return view('settings', compact('settings'));
    }

    /**
     * Simpan settings jadwal
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'check_in_time' => 'required|date_format:H:i',
            'check_out_time' => 'required|date_format:H:i',
            'check_in_before_minutes' => 'required|integer|min:0|max:120',
            'check_in_after_minutes' => 'required|integer|min:0|max:120',
        ]);

        // Simpan ke cache (permanent, unlimited)
        Cache::forever('check_in_time', $validated['check_in_time']);
        Cache::forever('check_out_time', $validated['check_out_time']);
        Cache::forever('check_in_before_minutes', $validated['check_in_before_minutes']);
        Cache::forever('check_in_after_minutes', $validated['check_in_after_minutes']);

        return redirect()->back()->with('success', 'Settings jadwal berhasil diupdate');
    }

    /**
     * API endpoint untuk get settings (digunakan oleh controller)
     */
    public function getSettings()
    {
        return response()->json([
            'check_in_time' => Cache::get('check_in_time', '07:00'),
            'check_out_time' => Cache::get('check_out_time', '17:00'),
            'check_in_before_minutes' => Cache::get('check_in_before_minutes', 30),
            'check_in_after_minutes' => Cache::get('check_in_after_minutes', 30),
        ]);
    }
}
