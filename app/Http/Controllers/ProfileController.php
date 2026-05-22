<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load(['student.class', 'teacher.institute']);
        return view('profil.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => ['required','email', Rule::unique('users')->ignore($user->id)],
            'current_password' => 'nullable|string',
            'password'         => 'nullable|string|min:6|confirmed',
            'no_whatsapp'      => 'nullable|string',
            'spesialisasi'     => 'nullable|string',
        ]);

        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama salah.']);
            }
        }

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
            ...(isset($validated['password']) ? ['password' => Hash::make($validated['password'])] : []),
        ]);

        if ($user->isGuru() && $user->teacher) {
            $user->teacher->update([
                'no_whatsapp' => $validated['no_whatsapp'] ?? $user->teacher->no_whatsapp,
                'spesialisasi' => $validated['spesialisasi'] ?? $user->teacher->spesialisasi,
            ]);
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
