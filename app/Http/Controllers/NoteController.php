<?php

namespace App\Http\Controllers;

use App\Models\CounselingNote;
use App\Models\Journal;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NoteController extends Controller
{
    public function index()
    {
        $user   = Auth::user();
        $teacher = $user->teacher;

        $notes = CounselingNote::with(['ticket.student.user', 'ticket.service'])
            ->when($teacher, fn($q) => $q->where('teacher_id', $teacher->id))
            ->latest()->paginate(15);

        return view('catatan.index', compact('notes'));
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

        return back()->with('success', 'Catatan konseling berhasil disimpan.');
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
}
