<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Teacher;
use App\Models\Service;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $q = Ticket::with(['student.user', 'student.class', 'teacher.user', 'service'])
            ->orderByDesc('is_favorite')
            ->orderByRaw("CASE status 
                WHEN 'menunggu' THEN 1 
                WHEN 'diproses' THEN 2 
                WHEN 'selesai' THEN 3 
                ELSE 4 
            END ASC")
            ->latest();

        if ($user->role === 'guru' && $user->teacher) {
            $q->where('teacher_id', $user->teacher->id);
        } elseif ($user->role === 'siswa' && $user->student) {
            $q->where('student_id', $user->student->id);
        }

        if ($request->status)  $q->where('status', $request->status);
        if ($request->service) $q->where('service_id', $request->service);
        if ($request->search) {
            $q->where(function($query) use ($request) {
                $query->where('title', 'like', '%'.$request->search.'%')
                      ->orWhereHas('student.user', function($u) use ($request) {
                          $u->where('name', 'like', '%'.$request->search.'%');
                      });
            });
        }

        $perPage = $request->integer('per_page', 25);
        if (!in_array($perPage, [5, 10, 25, 50, 100])) {
            $perPage = 25;
        }

        $tickets  = $q->paginate($perPage)->withQueryString();
        $services = Service::where('is_active', true)->get();
        $teachers = Teacher::with('user')->get();

        return view('tickets.index', compact('tickets', 'services', 'teachers'));
    }

    public function create()
    {
        $teachers = Teacher::with('user')->get();
        $services = Service::where('is_active', true)->get();
        return view('tickets.create', compact('teachers', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'prior_action' => 'nullable|string',
            'anonymous' => 'nullable|boolean',
        ]);

        // Auto assign to Guru BK of the class
        $class = auth()->user()->student->class;
        $validated['teacher_id'] = $class->teacher_id ?? null; // Can be null if admin hasn't assigned

        $validated['student_id'] = auth()->user()->student->id;
        $validated['anonymous'] = $request->has('anonymous');
        
        $ticket = Ticket::create($validated);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Tiket konseling berhasil dibuat.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorizeTicket($ticket);
        $ticket->load(['student.user', 'teacher.user', 'service', 'messages.sender', 'counselingNote', 'journal']);

        // Mark messages as read
        $ticket->messages()->where('sender_id', '!=', Auth::id())->update(['is_read' => true]);

        $teachers = Teacher::with('user')->get();
        return view('tickets.show', compact('ticket', 'teachers'));
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $this->authorizeTicket($ticket);
        $request->validate(['status' => 'required|in:menunggu,diproses,selesai']);

        $ticket->update(['status' => $request->status]);
        if ($request->status === 'selesai') {
            $ticket->update(['completed_at' => now()]);
        }

        return back()->with('success', 'Status tiket diperbarui.');
    }

    public function assignTeacher(Request $request, Ticket $ticket)
    {
        $request->validate(['teacher_id' => 'required|exists:teachers,id']);
        $ticket->update(['teacher_id' => $request->teacher_id, 'status' => 'diproses']);
        return back()->with('success', 'Guru BK berhasil ditugaskan.');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Tiket dihapus.');
    }

    public function toggleFavorite(Ticket $ticket)
    {
        $this->authorizeTicket($ticket);
        $ticket->update([
            'is_favorite' => !$ticket->is_favorite
        ]);
        
        $msg = $ticket->is_favorite ? 'Tiket difavoritkan.' : 'Favorit tiket dihapus.';
        return back()->with('success', $msg);
    }

    private function authorizeTicket(Ticket $ticket): void
    {
        $user = Auth::user();
        if ($user->isSiswa() && $ticket->student_id !== $user->student?->id) {
            abort(403);
        }
        if ($user->isGuru() && $ticket->teacher_id !== $user->teacher?->id) {
            abort(403);
        }
    }
}
