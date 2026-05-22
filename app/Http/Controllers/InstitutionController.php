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
            'npsn'          => 'nullable|string',
            'address'       => 'nullable|string',
            'phone'         => 'nullable|string',
            'email'         => 'nullable|email',
            'kepala_sekolah'=> 'nullable|string',
            'tahun_ajaran'  => 'nullable|string',
            'semester'      => 'nullable|in:Ganjil,Genap',
        ]);

        Institute::updateOrCreate(['id' => 1], $validated);
        return back()->with('success', 'Pengaturan lembaga berhasil disimpan.');
    }
}
