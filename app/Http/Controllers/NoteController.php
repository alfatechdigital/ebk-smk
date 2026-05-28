<?php

namespace App\Http\Controllers;

use App\Models\CounselingNote;
use App\Models\Journal;
use App\Models\Ticket;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $user   = Auth::user();
        $teacher = $user->teacher;

        $query = CounselingNote::with(['ticket.student.user', 'ticket.service'])
            ->when($teacher, fn($q) => $q->where('teacher_id', $teacher->id));

        if ($request->filled('month')) {
            $query->whereMonth('created_at', date('m', strtotime($request->month)))
                  ->whereYear('created_at', date('Y', strtotime($request->month)));
        }

        if ($request->filled('student_id')) {
            $query->whereHas('ticket', function($q) use ($request) {
                $q->where('student_id', $request->student_id);
            });
        }

        $notes = $query->latest()->paginate(15)->withQueryString();
        
        // Students for dropdown
        $students = Student::whereHas('tickets', function($q) use ($teacher) {
            if ($teacher) $q->where('teacher_id', $teacher->id);
        })->with('user')->get();

        return view('catatan.index', compact('notes', 'students'));
    }

    public function exportRekapPdf(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        $query = CounselingNote::with(['ticket.student.user', 'ticket.service'])
            ->when($teacher, fn($q) => $q->where('teacher_id', $teacher->id));

        if ($request->filled('month')) {
            $query->whereMonth('created_at', date('m', strtotime($request->month)))
                  ->whereYear('created_at', date('Y', strtotime($request->month)));
        }

        if ($request->filled('student_id')) {
            $query->whereHas('ticket', function($q) use ($request) {
                $q->where('student_id', $request->student_id);
            });
        }

        $notes = $query->oldest()->get();
        $student = $request->filled('student_id') ? Student::with('user')->find($request->student_id) : null;
        $month = $request->filled('month') ? date('F Y', strtotime($request->month)) : 'Semua Bulan';

        $pdf = Pdf::loadView('pdf.rekap_catatan', compact('notes', 'student', 'month', 'teacher'));
        return $pdf->download('Rekap_Catatan_Konseling.pdf');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ticket_id'  => 'required|exists:tickets,id',
            'title'      => 'required|string|max:255',
            'masalah'    => 'required|string',
            'tindakan'   => 'required|string',
            'kesimpulan' => 'nullable|string',
        ]);

        $validated['teacher_id'] = Auth::user()->teacher->id;
        $note = CounselingNote::create($validated);

        // Update ticket status
        $ticket = Ticket::find($validated['ticket_id']);
        $ticket->update([
            'status' => 'selesai',
            'completed_at' => now()
        ]);

        // Create Journal entry
        Journal::create([
            'ticket_id' => $ticket->id,
            'counseling_note_id' => $note->id,
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Sesi konseling selesai dan Catatan berhasil disimpan.');
    }

    public function generatePdf(CounselingNote $note)
    {
        $note->load(['ticket.student.user', 'ticket.service', 'teacher.user']);

        $pdf = Pdf::loadView('pdf.catatan', compact('note'));
        $filename = 'jurnal-' . $note->ticket->code . '-' . now()->format('Ymd') . '.pdf';
        $path = 'journals/' . $filename;

        Storage::disk('public')->put($path, $pdf->output());

        Journal::updateOrCreate(
            ['ticket_id' => $note->ticket_id, 'counseling_note_id' => $note->id],
            ['pdf_path' => $path]
        );

        return $pdf->download($filename);
    }

    public function update(Request $request, CounselingNote $note)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'masalah'    => 'required|string',
            'tindakan'   => 'required|string',
            'kesimpulan' => 'nullable|string',
        ]);
        $note->update($validated);
        return back()->with('success', 'Catatan berhasil diperbarui.');
    }

    public function destroy(CounselingNote $note)
    {
        $note->delete();
        return back()->with('success', 'Catatan berhasil dihapus.');
    }
}
