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
        
        $rules = [
            'email'            => ['required','email', Rule::unique('users')->ignore($user->id)],
            'current_password' => 'nullable|string',
            'password'         => 'nullable|string|min:6|confirmed',
        ];

        if ($user->isGuru()) {
            $rules['name'] = 'required|string|max:255';
            $rules['no_whatsapp'] = 'nullable|string';
            $rules['spesialisasi'] = 'nullable|string';
            $rules['nip'] = ['nullable', 'string', Rule::unique('teachers', 'nip')->ignore($user->teacher->id ?? 0)];
        } elseif ($user->isSiswa()) {
            $rules['no_hp'] = 'nullable|string|max:20';
        } else {
            $rules['name'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama salah.']);
            }
        }

        $userData = [
            'email' => $validated['email'],
        ];

        if (!$user->isSiswa() && isset($validated['name'])) {
            $userData['name'] = $validated['name'];
        }

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        if ($user->isGuru() && $user->teacher) {
            $user->teacher->update([
                'nip'          => $validated['nip'] ?? $user->teacher->nip,
                'no_whatsapp'  => $validated['no_whatsapp'] ?? $user->teacher->no_whatsapp,
                'spesialisasi' => $validated['spesialisasi'] ?? $user->teacher->spesialisasi,
            ]);
        } elseif ($user->isSiswa() && $user->student) {
            $user->student->update([
                'no_hp' => $validated['no_hp'] ?? null,
            ]);
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
