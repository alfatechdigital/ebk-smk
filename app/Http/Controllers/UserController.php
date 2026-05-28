<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\StudentsImport, $request->file('file'));
            return back()->with('success', 'Data siswa berhasil diimport.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    public function index(Request $request)
    {
        $q = User::with(['student.class', 'teacher'])->latest();

        if ($request->search) {
            $q->where(function($query) use ($request) {
                $query->where('name', 'like', '%'.$request->search.'%')
                      ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->role) $q->where('role', $request->role);

        $users   = $q->paginate(15);
        $classes = SchoolClass::all();

        return view('users.index', compact('users', 'classes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:superadmin,admin,guru,siswa',
            'nis_nip'  => 'nullable|string',
            'class_id' => 'nullable|exists:classes,id',
            'teacher_class_ids' => 'nullable|array',
            'teacher_class_ids.*' => 'exists:classes,id'
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        if ($validated['role'] === 'siswa') {
            Student::create(['user_id' => $user->id, 'class_id' => $validated['class_id'] ?? null, 'nis' => $validated['nis_nip'] ?? null]);
        } elseif ($validated['role'] === 'guru') {
            $teacher = Teacher::create(['user_id' => $user->id, 'nip' => $validated['nis_nip'] ?? null]);
            if (!empty($validated['teacher_class_ids'])) {
                SchoolClass::whereIn('id', $validated['teacher_class_ids'])->update(['teacher_id' => $teacher->id]);
            }
        }

        return back()->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,'.$user->id,
            'role'     => 'required|in:superadmin,admin,guru,siswa',
            'password' => 'nullable|string|min:6',
            'class_id' => 'nullable|exists:classes,id',
            'nis_nip'  => 'nullable|string',
            'teacher_class_ids' => 'nullable|array',
            'teacher_class_ids.*' => 'exists:classes,id'
        ]);

        $user->update(array_filter([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'role'     => $validated['role'],
            'password' => isset($validated['password']) ? Hash::make($validated['password']) : null,
        ]));

        if ($validated['role'] === 'siswa') {
            $student = $user->student;
            if (!$student) {
                Student::create(['user_id' => $user->id, 'class_id' => $validated['class_id'] ?? null, 'nis' => $validated['nis_nip'] ?? null]);
            } else {
                $student->update(['class_id' => $validated['class_id'] ?? null, 'nis' => $validated['nis_nip'] ?? null]);
            }
        } elseif ($validated['role'] === 'guru') {
            $teacher = $user->teacher;
            if (!$teacher) {
                $teacher = Teacher::create(['user_id' => $user->id, 'nip' => $validated['nis_nip'] ?? null]);
            } else {
                $teacher->update(['nip' => $validated['nis_nip'] ?? null]);
            }
            
            SchoolClass::where('teacher_id', $teacher->id)->update(['teacher_id' => null]);
            if (!empty($validated['teacher_class_ids'])) {
                SchoolClass::whereIn('id', $validated['teacher_class_ids'])->update(['teacher_id' => $teacher->id]);
            }
        }

        return back()->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
