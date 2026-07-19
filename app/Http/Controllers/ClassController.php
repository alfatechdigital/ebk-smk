<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\Institute;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $q = SchoolClass::with(['teacher.user', 'students'])->orderBy('name', 'asc');

        if ($request->search) {
            $q->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('wali_kelas', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->teacher_id) {
            $q->where('teacher_id', $request->teacher_id);
        }

        if ($request->tingkat) {
            $tingkat = $request->tingkat;
            if ($tingkat === 'X') {
                $q->where(function ($query) {
                    $query->where('name', 'like', 'X %')
                          ->where('name', 'not like', 'XI %')
                          ->where('name', 'not like', 'XII %');
                });
            } elseif ($tingkat === 'XI') {
                $q->where(function ($query) {
                    $query->where('name', 'like', 'XI %')
                          ->where('name', 'not like', 'XII %');
                });
            } elseif ($tingkat === 'XII') {
                $q->where('name', 'like', 'XII %');
            }
        }

        $perPage = $request->integer('per_page', 15);
        if (!in_array($perPage, [5, 10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $classes = $q->paginate($perPage)->withQueryString();
        $teachers = Teacher::with('user')->get();

        return view('kelas.index', compact('classes', 'teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255|unique:classes,name',
            'wali_kelas' => 'nullable|string|max:255',
            'teacher_id' => 'nullable|exists:teachers,id',
        ], [
            'name.unique' => 'Nama kelas sudah terdaftar. Harap gunakan nama kelas lain.',
        ]);

        SchoolClass::create([
            'name'         => $validated['name'],
            'wali_kelas'   => $validated['wali_kelas'] ?? null,
            'teacher_id'   => $validated['teacher_id'] ?? null,
            'institute_id' => Institute::first()->id ?? null,
        ]);

        return back()->with('success', 'Kelas baru berhasil ditambahkan.');
    }

    public function update(Request $request, SchoolClass $kelas)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255|unique:classes,name,' . $kelas->id,
            'wali_kelas' => 'nullable|string|max:255',
            'teacher_id' => 'nullable|exists:teachers,id',
        ], [
            'name.unique' => 'Nama kelas sudah terdaftar. Harap gunakan nama kelas lain.',
        ]);

        $kelas->update([
            'name'       => $validated['name'],
            'wali_kelas' => $validated['wali_kelas'] ?? null,
            'teacher_id' => $validated['teacher_id'] ?? null,
        ]);

        return back()->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $kelas)
    {
        $kelas->delete();
        return back()->with('success', 'Kelas berhasil dihapus.');
    }
}
