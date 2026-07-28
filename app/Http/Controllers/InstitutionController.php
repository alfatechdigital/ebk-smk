<?php

namespace App\Http\Controllers;

use App\Models\Institute;
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
    public function index()
    {
        $institute = Institute::first() ?? new Institute();
        return view('pengaturan.index', compact('institute'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'address'       => 'nullable|string',
            'phone'         => 'nullable|string',
            'kepala_sekolah'=> 'nullable|string',
            'kota_ttd'      => 'nullable|string',
            'media_expiry_days' => 'nullable|integer|min:1',
        ]);

        Institute::updateOrCreate(['id' => 1], $validated);
        return back()->with('success', 'Pengaturan lembaga berhasil disimpan.');
    }
}
