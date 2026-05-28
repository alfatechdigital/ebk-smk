@extends('layouts.app')
@section('title', 'Detail Tiket')
@section('page-title', 'Detail Tiket')

@section('content')
<div class="ticket-detail-header">
    <h3>{{ $ticket->title }}</h3>
    <div class="ticket-detail-meta">
        <span><i class="fas fa-hashtag"></i> {{ $ticket->code }}</span>
        <span><i class="fas fa-tag"></i> {{ $ticket->service?->name ?? '-' }}</span>
        <span><i class="fas fa-user"></i> 
            @if(auth()->user()->isSiswa())
                {{ $ticket->anonymous ? 'Kamu (Anonim)' : $ticket->student?->user?->name ?? '-' }}
            @else
                {{ $ticket->student?->user?->name ?? '-' }} 
                @if($ticket->anonymous) <span class="badge badge-warning" style="font-size:10px;padding:2px 6px;margin-left:4px">Anonim</span> @endif
            @endif
        </span>
        <span><i class="fas fa-chalkboard-teacher"></i> {{ $ticket->teacher?->user?->name ?? 'Belum ditugaskan' }}</span>
        <span class="badge {{ $ticket->status_badge }}">{{ $ticket->status_label }}</span>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">Deskripsi</div></div>
        <p style="line-height:1.7;color:var(--slate)">{{ $ticket->description }}</p>
        @if($ticket->prior_action)
        <div style="margin-top:16px"><strong>Tindakan Sebelumnya:</strong><p class="text-muted" style="margin-top:4px">{{ $ticket->prior_action }}</p></div>
        @endif
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">Aksi</div></div>
        <div style="display:flex;flex-direction:column;gap:10px">
            <a href="{{ route('chat.show', $ticket) }}" class="btn btn-primary" style="justify-content:center"><i class="fas fa-comments"></i> Buka Chat</a>
            @if(auth()->user()->isAdmin() && !$ticket->teacher_id)
            <form method="POST" action="{{ route('tickets.assign', $ticket) }}">
                @csrf
                <div class="field-group" style="margin-bottom:8px">
                    <label>Tugaskan Guru BK</label>
                    <select name="teacher_id" required>
                        @foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->user->name }}</option>@endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center"><i class="fas fa-user-check"></i> Tugaskan</button>
            </form>
            @endif
            @if(auth()->user()->isGuru() && $ticket->status !== 'selesai')
            <form method="POST" action="{{ route('tickets.status', $ticket) }}">
                @csrf <input type="hidden" name="status" value="selesai">
                <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center" onclick="return confirm('Selesaikan?')"><i class="fas fa-check-double"></i> Selesaikan</button>
            </form>
            @endif
        </div>
    </div>
</div>

@if($ticket->counselingNote)
<div class="card mt-20">
    <div class="card-header"><div class="card-title">Catatan Konseling</div>
        <a href="{{ route('catatan.pdf', $ticket->counselingNote) }}" class="btn btn-secondary btn-sm"><i class="fas fa-file-pdf"></i> Download PDF</a>
    </div>
    <p><strong>Masalah:</strong> {{ $ticket->counselingNote->masalah }}</p>
    <p style="margin-top:8px"><strong>Tindakan:</strong> {{ $ticket->counselingNote->tindakan }}</p>
</div>
@endif
@endsection
