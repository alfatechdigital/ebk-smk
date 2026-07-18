<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $tickets = $this->getUserTickets($user)->with(['student.user', 'student.class', 'teacher.user', 'service'])->latest()->get();
        $active  = $tickets->first();

        $messages = $active
            ? $active->messages()->with('sender')->get()
            : collect();

        if ($active) {
            $active->messages()->where('sender_id', '!=', $user->id)->update(['is_read' => true]);
        }

        return view('chat.index', compact('tickets', 'active', 'messages'));
    }

    public function show(Ticket $ticket)
    {
        $this->gate($ticket);
        $user    = Auth::user();
        $tickets = $this->getUserTickets($user)->with(['student.user', 'student.class', 'teacher.user', 'service'])->latest()->get();
        $ticket->load(['student.user', 'student.class', 'teacher.user', 'service', 'messages.sender']);

        $ticket->messages()->where('sender_id', '!=', $user->id)->update(['is_read' => true]);

        return view('chat.index', ['tickets' => $tickets, 'active' => $ticket, 'messages' => $ticket->messages]);
    }

    public function sendMessage(Request $request, Ticket $ticket)
    {
        $this->gate($ticket);

        $request->validate([
            'content' => 'nullable|string|max:5000',
            'file'    => 'nullable|file|max:10240', // 10 MB
        ]);

        $data = [
            'ticket_id' => $ticket->id,
            'sender_id' => Auth::id(),
            'type'      => 'text',
            'content'   => $request->input('content'),
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $mime = $file->getMimeType();
            $type = 'file';
            if (str_starts_with($mime, 'image/')) $type = 'image';
            elseif (str_starts_with($mime, 'video/')) $type = 'video';
            elseif (str_starts_with($mime, 'audio/')) $type = 'audio';

            $path = $file->store("chat/{$ticket->id}", 'public');
            $data['type']      = $type;
            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = intval($file->getSize() / 1024);
        }

        $message = TicketMessage::create($data);

        // Broadcast the message via Pusher
        broadcast(new \App\Events\MessageSent($message))->toOthers();

        // Update ticket status to 'diproses' if it was 'menunggu' and the sender is a Guru BK (teacher)
        if ($ticket->status === 'menunggu' && Auth::user()->isGuru()) {
            $ticket->update(['status' => 'diproses']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $message->load('sender'),
                'ticket'  => $ticket->fresh(),
            ]);
        }

        return back();
    }

    public function messages(Ticket $ticket)
    {
        $this->gate($ticket);
        $user = Auth::user();

        // Mark incoming messages as read when user polls
        $ticket->messages()->where('sender_id', '!=', $user->id)->where('is_read', false)->update(['is_read' => true]);

        $messages = $ticket->messages()->with('sender')->get()->map(fn($m) => [
            'id'        => $m->id,
            'type'      => $m->type,
            'content'   => $m->content,
            'file_url'  => $m->file_url,
            'file_name' => $m->file_name,
            'sender'    => ['id' => $m->sender_id, 'name' => $m->sender->name, 'initials' => $m->sender->avatar_initials],
            'time'      => $m->created_at->format('H:i'),
            'is_me'     => $m->sender_id === Auth::id(),
        ]);

        return response()->json($messages);
    }

    private function getUserTickets($user)
    {
        $q = Ticket::query();
        if ($user->isGuru() && $user->teacher) {
            $q->where('teacher_id', $user->teacher->id);
        } elseif ($user->isSiswa() && $user->student) {
            $q->where('student_id', $user->student->id);
        }
        return $q;
    }

    private function gate(Ticket $ticket): void
    {
        $user = Auth::user();
        if ($user->isSiswa() && $ticket->student_id !== $user->student?->id) abort(403);
        if ($user->isGuru() && $ticket->teacher_id !== $user->teacher?->id) abort(403);
    }
}
