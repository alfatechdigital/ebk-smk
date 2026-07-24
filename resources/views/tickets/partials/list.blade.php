<div class="ticket-list" style="display: flex; flex-direction: column; gap: 12px;">
    @forelse ($tickets as $ticket)
        @include('tickets.partials.card', ['ticket' => $ticket])
    @empty
    <div class="empty-state" style="width: 100%;">
        <i class="fas fa-ticket-alt"></i>
        <p>Belum ada tiket konsultasi</p>
        @if(auth()->user()->isSiswa() && auth()->user()->student?->class && auth()->user()->student?->class?->teacher_id)
            <a href="{{ route('tickets.create') }}" class="btn btn-primary mt-20"><i class="fas fa-plus"></i> Ajukan Konsultasi</a>
        @endif
    </div>
    @endforelse
</div>
{{ $tickets->links('vendor.pagination.custom') }}
