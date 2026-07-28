<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $query = Journal::with(['ticket.student.user', 'counselingNote.teacher.user', 'ticket.service']);

        if ($request->filled('month')) {
            $query->whereMonth('created_at', date('m', strtotime($request->month)))
                  ->whereYear('created_at', date('Y', strtotime($request->month)));
        }

        if ($request->filled('teacher_id')) {
            $query->whereHas('counselingNote', function($q) use ($request) {
                $q->where('teacher_id', $request->teacher_id);
            });
        }

        $journals = $query->latest()->paginate(15)->withQueryString();
        $teachers = Teacher::with('user')->get();

        return view('jurnal.index', compact('journals', 'teachers'));
    }

    public function exportPdf(Request $request)
    {
        $query = Journal::with(['ticket.student.user', 'counselingNote.teacher.user', 'ticket.service']);

        if ($request->filled('month')) {
            $query->whereMonth('created_at', date('m', strtotime($request->month)))
                  ->whereYear('created_at', date('Y', strtotime($request->month)));
        }

        if ($request->filled('teacher_id')) {
            $query->whereHas('counselingNote', function($q) use ($request) {
                $q->where('teacher_id', $request->teacher_id);
            });
        }

        $journals = $query->oldest()->get();
        $teacher = $request->filled('teacher_id') ? Teacher::with('user')->find($request->teacher_id) : null;
        $month = $request->filled('month') ? \Carbon\Carbon::parse($request->month)->locale('id')->translatedFormat('F Y') : 'Semua Bulan';

        $pdf = Pdf::loadView('pdf.rekap_jurnal', compact('journals', 'teacher', 'month'));
        return $pdf->download('Jurnal_Kegiatan_BK.pdf');
    }
}
