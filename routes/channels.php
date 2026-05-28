<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('ticket.{ticket_id}', function ($user, $ticket_id) {
    $ticket = \App\Models\Ticket::find($ticket_id);
    if (!$ticket) return false;

    if ($user->isSiswa() && $ticket->student_id === $user->student?->id) return true;
    if ($user->isGuru() && $ticket->teacher_id === $user->teacher?->id) return true;

    return false;
});
