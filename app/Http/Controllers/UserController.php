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
        ini_set('max_execution_time', 300);
        set_time_limit(300);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $forceUpdate = $request->boolean('force_update', false);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\StudentsImport($forceUpdate), $request->file('file'));
            return redirect()->route('users.index', ['role' => 'siswa', 'import_success' => '1']);
        } catch (\Exception $e) {
            return redirect()->route('users.index', ['role' => 'siswa', 'import_error' => $e->getMessage()]);
        }
    }

    public function importGuru(Request $request)
    {
        ini_set('max_execution_time', 300);
        set_time_limit(300);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $forceUpdate = $request->boolean('force_update', false);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\TeachersImport($forceUpdate), $request->file('file'));
            return redirect()->route('users.index', ['role' => 'guru', 'import_success' => '1']);
        } catch (\Exception $e) {
            return redirect()->route('users.index', ['role' => 'guru', 'import_error' => $e->getMessage()]);
        }
    }

    public function importGuruCheck(Request $request)
    {
        ini_set('max_execution_time', 300);
        set_time_limit(300);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            $rows = \Maatwebsite\Excel\Facades\Excel::toCollection(new \App\Imports\TeachersImport, $request->file('file'))->first();
            
            $nipList = [];
            $emailList = [];
            foreach ($rows as $row) {
                if (empty($row['nama'])) {
                    continue;
                }
                $nip = !empty($row['nip']) ? trim($row['nip']) : null;
                $email = !empty($row['email']) ? trim($row['email']) : null;
                if ($nip) {
                    $nipList[] = $nip;
                }
                if ($email) {
                    $emailList[] = $email;
                }
            }

            $duplicateNipList = [];
            if (!empty($nipList)) {
                $duplicateNipList = \App\Models\Teacher::whereIn('nip', $nipList)->pluck('nip')->toArray();
            }

            $duplicateEmailList = [];
            if (!empty($emailList)) {
                $duplicateEmailList = \App\Models\User::whereIn('email', $emailList)->pluck('email')->toArray();
            }

            $hasDuplicates = count($duplicateNipList) > 0 || count($duplicateEmailList) > 0;

            return response()->json([
                'has_duplicates' => $hasDuplicates,
                'duplicates_count' => count($duplicateNipList) + count($duplicateEmailList),
                'duplicate_nips' => $duplicateNipList,
                'duplicate_emails' => $duplicateEmailList
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function importCheck(Request $request)
    {
        ini_set('max_execution_time', 300);
        set_time_limit(300);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            $rows = \Maatwebsite\Excel\Facades\Excel::toCollection(new \App\Imports\StudentsImport, $request->file('file'))->first();
            
            $nisList = [];
            foreach ($rows as $row) {
                if (empty($row['nama'])) {
                    continue;
                }
                $nis = !empty($row['nis']) ? trim($row['nis']) : null;
                if ($nis) {
                    $nisList[] = $nis;
                }
            }

            $duplicateNisList = [];
            if (!empty($nisList)) {
                $duplicateNisList = \App\Models\Student::whereIn('nis', $nisList)->pluck('nis')->toArray();
            }

            return response()->json([
                'has_duplicates' => count($duplicateNisList) > 0,
                'duplicates_count' => count($duplicateNisList),
                'duplicates' => $duplicateNisList
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function promoteClasses(Request $request)
    {
        // Snapshot all class counselors before we begin mutating
        $classCounselors = SchoolClass::all()->pluck('teacher_id', 'id')->toArray();

        $students = Student::whereNotNull('class_id')->with('class')->get();
        $promotedCount = 0;
        $graduatedCount = 0;

        foreach ($students as $student) {
            $class = $student->class;
            if (!$class) continue;

            $className = $class->name;
            $nextClassName = null;

            if (preg_match('/^X\b/i', $className)) {
                if (!preg_match('/^XI\b/i', $className)) {
                    $nextClassName = preg_replace('/^X\b/i', 'XI', $className);
                }
            }
            
            if (!$nextClassName && preg_match('/^XI\b/i', $className)) {
                $nextClassName = preg_replace('/^XI\b/i', 'XII', $className);
            }

            if ($nextClassName) {
                $nextClass = SchoolClass::where('name', $nextClassName)->first();
                if (!$nextClass) {
                    $nextClass = SchoolClass::create([
                        'name' => $nextClassName,
                        'institute_id' => $class->institute_id,
                        'wali_kelas' => 'Wali Kelas ' . $nextClassName,
                        'archived' => 0
                    ]);
                }
                $student->update(['class_id' => $nextClass->id]);
                $promotedCount++;
            } elseif (preg_match('/^XII\b/i', $className)) {
                $student->update(['class_id' => null]);
                if ($student->user) {
                    $student->user->update(['is_active' => 0]);
                }
                $graduatedCount++;
            }
        }

        // Shift class counselors upwards
        $newAssignments = [];
        $allClasses = SchoolClass::all();

        foreach ($allClasses as $c) {
            $className = $c->name;
            $nextClassName = null;

            if (preg_match('/^X\b/i', $className)) {
                if (!preg_match('/^XI\b/i', $className)) {
                    $nextClassName = preg_replace('/^X\b/i', 'XI', $className);
                }
            }
            
            if (!$nextClassName && preg_match('/^XI\b/i', $className)) {
                $nextClassName = preg_replace('/^XI\b/i', 'XII', $className);
            }

            if ($nextClassName) {
                $nextClass = SchoolClass::where('name', $nextClassName)->first();
                if ($nextClass) {
                    $newAssignments[$nextClass->id] = $classCounselors[$c->id] ?? null;
                }
            }
        }

        // Apply new counselor assignments to all classes
        foreach ($allClasses as $c) {
            $newTeacherId = $newAssignments[$c->id] ?? null;
            $c->update(['teacher_id' => $newTeacherId]);
        }

        return back()->with('promote_success', "Berhasil menaikkan kelas untuk {$promotedCount} siswa dan meluluskan {$graduatedCount} siswa.");
    }

    public function deleteGraduated(Request $request)
    {
        $graduatedUsers = User::where('role', 'siswa')->where('is_active', 0)->get();
        $count = $graduatedUsers->count();

        if ($count === 0) {
            return back()->with('error', 'Tidak ditemukan siswa berstatus lulus untuk dihapus.');
        }

        foreach ($graduatedUsers as $user) {
            $user->delete();
        }

        return back()->with('delete_graduated_success', "Berhasil menghapus {$count} siswa berstatus lulus beserta seluruh data akunnya secara permanen.");
    }

    public function index(Request $request)
    {
        $q = User::with(['student.class.teacher.user', 'teacher'])->latest();

        if ($request->search) {
            $q->where(function($query) use ($request) {
                $query->where('name', 'like', '%'.$request->search.'%')
                      ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->role) {
            $q->where('role', $request->role);
        }

        if ($request->role === 'siswa') {
            if ($request->filled('status')) {
                if ($request->status === 'lulus') {
                    $q->where('is_active', 0);
                } elseif ($request->status === 'aktif') {
                    $q->where('is_active', 1);
                }
            }

            if ($request->filled('class_id')) {
                $q->whereHas('student', function($sq) use ($request) {
                    $sq->where('class_id', $request->class_id);
                });
            }

            if ($request->filled('teacher_id')) {
                $q->whereHas('student.class', function($cq) use ($request) {
                    $cq->where('teacher_id', $request->teacher_id);
                });
            }
        }

        $users = $q->paginate($request->per_page ?? 25)->appends(request()->except(['import_success', 'import_error']));
        $classes = SchoolClass::orderBy('name')->get();
        $teachers = Teacher::with('user')->get();

        return view('users.index', compact('users', 'classes', 'teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => $request->role === 'siswa' ? 'nullable|email|unique:users,email' : 'required|email|unique:users,email',
            'password'      => 'required|string|min:6',
            'role'          => 'required|in:admin,guru,siswa',
            'nis_nip'       => 'nullable|string',
            'class_id'      => 'nullable|exists:classes,id',
            'no_hp'         => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:L,P',
            'spesialisasi'  => 'nullable|string|max:255',
        ]);

        if ($request->role === 'siswa' && $request->filled('nis_nip')) {
            $request->validate([
                'nis_nip' => 'unique:students,nis',
            ], [
                'nis_nip.unique' => 'NIS sudah digunakan oleh siswa lain.',
            ]);
        }

        $email = $validated['email'] ?? null;

        $user = User::create([
            'name'          => $validated['name'],
            'email'         => $email,
            'password'      => Hash::make($validated['password']),
            'role'          => $validated['role'],
            'no_hp'         => $validated['no_hp'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
        ]);

        if ($validated['role'] === 'siswa') {
            Student::create([
                'user_id'  => $user->id,
                'class_id' => $validated['class_id'] ?? null,
                'nis'      => $validated['nis_nip'] ?? null,
                'no_hp'    => $validated['no_hp'] ?? null
            ]);
        } elseif ($validated['role'] === 'guru') {
            Teacher::create([
                'user_id'     => $user->id,
                'nip'         => $validated['nis_nip'] ?? null,
                'no_whatsapp' => $validated['no_hp'] ?? null,
                'spesialisasi'=> $validated['spesialisasi'] ?? null
            ]);
        }

        return back()->with('success_modal', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => $request->role === 'siswa' ? 'nullable|email|unique:users,email,'.$user->id : 'required|email|unique:users,email,'.$user->id,
            'role'          => 'required|in:admin,guru,siswa',
            'password'      => 'nullable|string|min:6',
            'class_id'      => 'nullable|exists:classes,id',
            'nis_nip'       => 'nullable|string',
            'no_hp'         => 'nullable|string|max:20',
            'jenis_kelamin' => 'nullable|in:L,P',
            'spesialisasi'  => 'nullable|string|max:255',
        ]);

        if ($request->role === 'siswa' && $request->filled('nis_nip')) {
            $studentId = $user->student?->id;
            $request->validate([
                'nis_nip' => 'unique:students,nis' . ($studentId ? ',' . $studentId : ''),
            ], [
                'nis_nip.unique' => 'NIS sudah digunakan oleh siswa lain.',
            ]);
        }

        $email = $validated['email'] ?? null;

        $updateData = [
            'name'          => $validated['name'],
            'email'         => $email,
            'role'          => $validated['role'],
            'no_hp'         => $validated['no_hp'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        if ($validated['role'] === 'siswa') {
            $student = $user->student;
            if (!$student) {
                Student::create([
                    'user_id'  => $user->id,
                    'class_id' => $validated['class_id'] ?? null,
                    'nis'      => $validated['nis_nip'] ?? null,
                    'no_hp'    => $validated['no_hp'] ?? null
                ]);
            } else {
                $student->update([
                    'class_id' => $validated['class_id'] ?? null,
                    'nis'      => $validated['nis_nip'] ?? null,
                    'no_hp'    => $validated['no_hp'] ?? null
                ]);
            }
        } elseif ($validated['role'] === 'guru') {
            $teacher = $user->teacher;
            if (!$teacher) {
                $teacher = Teacher::create([
                    'user_id'     => $user->id,
                    'nip'         => $validated['nis_nip'] ?? null,
                    'no_whatsapp' => $validated['no_hp'] ?? null,
                    'spesialisasi'=> $validated['spesialisasi'] ?? null
                ]);
            } else {
                $teacher->update([
                    'nip'         => $validated['nis_nip'] ?? null,
                    'no_whatsapp' => $validated['no_hp'] ?? null,
                    'spesialisasi'=> $validated['spesialisasi'] ?? null
                ]);
            }
        }

        return back()->with('success_modal', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success_modal', 'Pengguna berhasil dihapus.');
    }
}
