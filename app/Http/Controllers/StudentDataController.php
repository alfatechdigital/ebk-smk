<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentDataController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::with(['user', 'class', 'tickets'])
            ->when($request->search, fn($q) => $q->whereHas('user', fn($u) => $u->where('name','like','%'.$request->search.'%')))
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->latest()->paginate(20);

        $classes = \App\Models\SchoolClass::all();

        return view('data-siswa.index', compact('students', 'classes'));
    }
}
