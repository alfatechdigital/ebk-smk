<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentDataController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 25);
        if (!in_array($perPage, [5, 10, 25, 50, 100])) {
            $perPage = 25;
        }

        $user = auth()->user();
        $teacher = $user->teacher;

        $students = Student::with(['user', 'class', 'tickets'])
            ->when($user->role === 'guru' && $teacher, function ($q) use ($teacher) {
                $q->whereIn('class_id', $teacher->classes->pluck('id'));
            })
            ->when($request->search, fn($q) => $q->whereHas('user', fn($u) => $u->where('name', 'like', '%' . $request->search . '%')))
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->latest()->paginate($perPage)->withQueryString();

        if ($user->role === 'guru' && $teacher) {
            $classes = $teacher->classes;
        } else {
            $classes = \App\Models\SchoolClass::all();
        }

        return view('data-siswa.index', compact('students', 'classes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:6',
            'class_id'      => 'required|exists:classes,id',
            'nis'           => 'required|string|unique:students,nis',
            'no_hp'         => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:L,P',
        ], [
            'nis.unique' => 'NIS sudah terdaftar di sistem. Harap gunakan NIS lain.',
            'email.unique' => 'Email sudah terdaftar di sistem. Harap gunakan email lain.',
        ]);

        $user = User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'role'          => 'siswa',
            'no_hp'         => $validated['no_hp'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
        ]);

        Student::create([
            'user_id'  => $user->id,
            'class_id' => $validated['class_id'],
            'nis'      => $validated['nis'],
            'no_hp'    => $validated['no_hp'] ?? null,
        ]);

        return back()->with('success', 'Siswa baru berhasil ditambahkan.');
    }

    public function update(Request $request, Student $student)
    {
        $user = $student->user;
        
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'password'      => 'nullable|string|min:6',
            'class_id'      => 'required|exists:classes,id',
            'nis'           => 'required|string|unique:students,nis,' . $student->id,
            'no_hp'         => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:L,P',
        ], [
            'nis.unique' => 'NIS sudah terdaftar di sistem. Harap gunakan NIS lain.',
            'email.unique' => 'Email sudah terdaftar di sistem. Harap gunakan email lain.',
        ]);

        $userData = [
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'no_hp'         => $validated['no_hp'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        $student->update([
            'class_id' => $validated['class_id'],
            'nis'      => $validated['nis'],
            'no_hp'    => $validated['no_hp'] ?? null,
        ]);

        return back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student)
    {
        if ($student->user) {
            $student->user->delete();
        } else {
            $student->delete();
        }
        return back()->with('success', 'Data siswa berhasil dihapus.');
    }
}
