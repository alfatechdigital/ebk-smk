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

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } elseif ($request->filled('month')) {
            $query->whereMonth('created_at', date('m', strtotime($request->month)))
                  ->whereYear('created_at', date('Y', strtotime($request->month)));
        }

        if ($request->filled('student_id')) {
            $query->whereHas('ticket', function($q) use ($request) {
                $q->where('student_id', $request->student_id);
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('ticket.student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('service_id')) {
            $query->whereHas('ticket', function($q) use ($request) {
                $q->where('service_id', $request->service_id);
            });
        }

        $perPage = $request->integer('per_page', 25);
        if (!in_array($perPage, [5, 10, 25, 50, 100])) {
            $perPage = 25;
        }

        $notes = $query->latest()->paginate($perPage)->withQueryString();
        
        // Students for dropdown
        $students = Student::whereHas('tickets', function($q) use ($teacher) {
            if ($teacher) $q->where('teacher_id', $teacher->id);
        })->with('user')->get();

        // Classes for dropdown
        $classes = \App\Models\SchoolClass::when($teacher, function($q) use ($teacher) {
            $q->where('teacher_id', $teacher->id);
        })->get();

        // Services for dropdown
        $services = \App\Models\Service::where('is_active', true)->get();

        // All students for manual note creation
        $allStudents = Student::when($teacher, function($q) use ($teacher) {
            $q->whereHas('class', function($c) use ($teacher) {
                $c->where('teacher_id', $teacher->id);
            });
        })->with(['user', 'class'])->get();

        return view('catatan.index', compact('notes', 'students', 'classes', 'services', 'allStudents'));
    }

    public function exportRekapPdf(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        $query = CounselingNote::with(['ticket.student.user', 'ticket.service'])
            ->when($teacher, fn($q) => $q->where('teacher_id', $teacher->id));

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } elseif ($request->filled('month')) {
            $query->whereMonth('created_at', date('m', strtotime($request->month)))
                  ->whereYear('created_at', date('Y', strtotime($request->month)));
        }

        if ($request->filled('student_id')) {
            $query->whereHas('ticket', function($q) use ($request) {
                $q->where('student_id', $request->student_id);
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('ticket.student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('service_id')) {
            $query->whereHas('ticket', function($q) use ($request) {
                $q->where('service_id', $request->service_id);
            });
        }

        $notes = $query->oldest()->get();
        $student = $request->filled('student_id') ? Student::with('user')->find($request->student_id) : null;
        
        $month = 'Semua Bulan';
        if ($request->filled('date')) {
            $month = date('d F Y', strtotime($request->date));
        } elseif ($request->filled('month')) {
            $month = date('F Y', strtotime($request->month));
        }

        $pdf = Pdf::loadView('pdf.rekap_catatan', compact('notes', 'student', 'month', 'teacher'))->setPaper('a4', 'landscape');
        return $pdf->download('Rekap_Catatan_Konseling.pdf');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($request->has('student_id')) {
            // Manual Note
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'service_id' => 'required|exists:services,id',
                'created_at' => 'required|date',
                'title'      => 'required|string|max:255',
                'masalah'    => 'required|string',
                'tindakan'   => 'required|string',
                'kesimpulan' => 'nullable|string',
            ]);

            $student = Student::find($request->student_id);
            $ticketDate = date('Y-m-d H:i:s', strtotime($request->created_at . ' ' . date('H:i:s')));

            // 1. Create a dummy ticket
            $ticket = new Ticket([
                'student_id'   => $student->id,
                'teacher_id'   => $user->teacher ? $user->teacher->id : null,
                'service_id'   => $validated['service_id'],
                'title'        => $validated['title'],
                'description'  => $validated['masalah'],
                'status'       => 'selesai',
                'completed_at' => $ticketDate,
            ]);
            $ticket->created_at = $ticketDate;
            $ticket->updated_at = $ticketDate;
            $ticket->save();

            // 2. Create the counseling note
            $note = new CounselingNote([
                'ticket_id'  => $ticket->id,
                'teacher_id' => $user->teacher ? $user->teacher->id : null,
                'title'      => $validated['title'],
                'masalah'    => $validated['masalah'],
                'tindakan'   => $validated['tindakan'],
                'kesimpulan' => $validated['kesimpulan'] ?? null,
            ]);
            $note->created_at = $ticketDate;
            $note->updated_at = $ticketDate;
            $note->save();

            // 3. Create Journal entry
            $journal = new Journal([
                'ticket_id'          => $ticket->id,
                'counseling_note_id' => $note->id,
            ]);
            $journal->created_at = $ticketDate;
            $journal->updated_at = $ticketDate;
            $journal->save();

            return redirect()->route('catatan.index')->with('success', 'Catatan manual berhasil disimpan.');
        } else {
            // Ticket-based Note
            $validated = $request->validate([
                'ticket_id'  => 'required|exists:tickets,id',
                'title'      => 'required|string|max:255',
                'masalah'    => 'required|string',
                'tindakan'   => 'required|string',
                'kesimpulan' => 'nullable|string',
            ]);

            $validated['teacher_id'] = $user->teacher ? $user->teacher->id : null;
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
            'service_id' => 'required|exists:services,id',
            'masalah'    => 'required|string',
            'tindakan'   => 'required|string',
            'kesimpulan' => 'nullable|string',
        ]);
        
        $note->update([
            'title'      => $validated['title'],
            'masalah'    => $validated['masalah'],
            'tindakan'   => $validated['tindakan'],
            'kesimpulan' => $validated['kesimpulan'] ?? null,
        ]);

        if ($note->ticket) {
            $note->ticket->update([
                'service_id' => $validated['service_id'],
            ]);
        }

        return back()->with('success', 'Catatan berhasil diperbarui.');
    }

    public function destroy(CounselingNote $note)
    {
        $note->delete();
        return back()->with('success', 'Catatan berhasil dihapus.');
    }
}
