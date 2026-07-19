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
        $userId = $user->id;
        $q = Ticket::with(['student.user', 'student.class', 'teacher.user', 'service'])
            ->withCount(['messages as unread_count' => function($query) use ($userId) {
                $query->where('is_read', false)->where('sender_id', '!=', $userId);
            }])
            ->orderByDesc('is_pinned')
            ->orderByRaw("CASE status 
                WHEN 'menunggu' THEN 1 
                WHEN 'diproses' THEN 2 
                WHEN 'selesai' THEN 3 
                WHEN 'dibatalkan' THEN 3
                ELSE 4 
            END ASC")
            ->orderByDesc(
                \DB::raw("COALESCE(completed_at, cancelled_at, created_at)")
            );

        if ($user->role === 'guru' && $user->teacher) {
            $q->where('teacher_id', $user->teacher->id);
        } elseif ($user->role === 'siswa' && $user->student) {
            $q->where('student_id', $user->student->id);
        }

        if ($request->status)  $q->where('status', $request->status);
        if ($request->service) $q->where('service_id', $request->service);
        if ($request->favorite) {
            $q->where('is_favorite', true);
        }
        if ($request->anonymous) {
            $q->where('anonymous', true);
        }
        if ($request->unread) {
            $q->whereNotIn('status', ['selesai', 'dibatalkan'])
              ->whereHas('messages', function($query) use ($userId) {
                  $query->where('is_read', false)->where('sender_id', '!=', $userId);
              });
        }
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

        if ($request->ajax()) {
            return view('tickets.partials.list', compact('tickets'))->render();
        }

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
        $validated['class_id'] = $class->id ?? null;

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
        $request->validate([
            'status' => 'required|in:menunggu,diproses,selesai,dibatalkan',
            'cancel_reason' => 'required_if:status,dibatalkan|nullable|string'
        ]);

        // Only Guru BK is allowed to cancel a ticket
        if ($request->status === 'dibatalkan' && !auth()->user()->isGuru()) {
            abort(403, 'Hanya Guru BK yang dapat membatalkan konsultasi.');
        }

        $updateData = ['status' => $request->status];
        if ($request->status === 'selesai') {
            $updateData['completed_at'] = now();
        } elseif ($request->status === 'dibatalkan') {
            $updateData['cancelled_at'] = now();
            $updateData['cancel_reason'] = $request->cancel_reason;
        }

        $ticket->update($updateData);

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
        
        return back();
    }

    public function togglePinned(Ticket $ticket)
    {
        $this->authorizeTicket($ticket);
        $ticket->update([
            'is_pinned' => !$ticket->is_pinned
        ]);
        
        return back();
    }

    public function unreadCounts(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([], 401);
        }
        
        $userId = $user->id;
        $renderedIds = $request->input('rendered_ids', []);
        
        $q = Ticket::query();
        if ($user->role === 'guru' && $user->teacher) {
            $q->where('teacher_id', $user->teacher->id);
        } elseif ($user->role === 'siswa' && $user->student) {
            $q->where('student_id', $user->student->id);
        }
        
        // 1. Fetch updates (unread count, status, status_label) for currently rendered tickets
        $tickets = (clone $q)->whereIn('id', $renderedIds)
            ->withCount(['messages as unread_count' => function($query) use ($userId) {
                $query->where('is_read', false)->where('sender_id', '!=', $userId);
            }])
            ->get();
            
        $updates = [];
        foreach ($tickets as $ticket) {
            $updates[$ticket->id] = [
                'unread_count' => $ticket->unread_count,
                'status' => $ticket->status,
                'status_label' => $ticket->status_label,
            ];
        }
        
        // 2. Fetch any new tickets that are not in rendered_ids and match page filters
        $newTicketsQuery = (clone $q)->whereNotIn('id', $renderedIds);
        
        if (!empty($renderedIds)) {
            $maxId = max(array_map('intval', $renderedIds));
            $newTicketsQuery->where('id', '>', $maxId);
        }
        
        if ($request->status)  $newTicketsQuery->where('status', $request->status);
        if ($request->service) $newTicketsQuery->where('service_id', $request->service);
        if ($request->favorite) {
            $newTicketsQuery->where('is_favorite', true);
        }
        if ($request->anonymous) {
            $newTicketsQuery->where('anonymous', true);
        }
        if ($request->unread) {
            $newTicketsQuery->where('status', '!=', 'selesai')
              ->whereHas('messages', function($query) use ($userId) {
                  $query->where('is_read', false)->where('sender_id', '!=', $userId);
              });
        }
        if ($request->search) {
            $newTicketsQuery->where(function($query) use ($request) {
                $query->where('title', 'like', '%'.$request->search.'%')
                      ->orWhereHas('student.user', function($u) use ($request) {
                          $u->where('name', 'like', '%'.$request->search.'%');
                      });
            });
        }
        
        $newTickets = $newTicketsQuery->latest()->get();
        
        $newTicketsHtml = [];
        foreach ($newTickets as $t) {
            $t->loadCount(['messages as unread_count' => function($query) use ($userId) {
                $query->where('is_read', false)->where('sender_id', '!=', $userId);
            }]);
            $newTicketsHtml[] = view('tickets.partials.card', ['ticket' => $t])->render();
        }

        $totalUnread = \App\Models\TicketMessage::whereHas('ticket', function($query) use ($q) {
            $query->whereIn('id', (clone $q)->whereNotIn('status', ['selesai', 'dibatalkan'])->select('id'));
        })
        ->where('is_read', false)
        ->where('sender_id', '!=', $userId)
        ->count();
        
        return response()->json([
            'updates' => $updates,
            'new_tickets' => $newTicketsHtml,
            'total_unread' => $totalUnread
        ]);
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
