@extends('layouts.app')
@section('title', 'Detail Tiket')
@section('page-title', 'Detail Tiket')

@section('content')
<div class="page-header-row" style="margin-bottom: 20px;">
    <div class="page-header" style="margin-bottom: 0;">
        <h2>Detail Konsultasi</h2>
        <p style="margin-top: 4px; color: var(--muted); font-size: 13px;">
            Kode Tiket: <span style="font-weight: 700; color: var(--teal);">{{ $ticket->code }}</span> · Diajukan pada {{ $ticket->created_at->format('d M Y - H:i') }}
        </p>
    </div>
    <div style="flex-shrink: 0; text-align: right;">
        <span class="badge {{ $ticket->status_badge }}" style="padding: 6px 14px; font-size: 13px; border-radius: 6px;">{{ $ticket->status_label }}</span>
        @if($ticket->completed_at)
            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">Selesai: {{ $ticket->completed_at->format('d M Y - H:i') }}</div>
        @elseif($ticket->cancelled_at)
            <div style="font-size: 11px; color: #ef4444; margin-top: 4px;">Dibatalkan: {{ $ticket->cancelled_at->format('d M Y - H:i') }}</div>
        @endif
    </div>
</div>

<div class="grid-3">
    <!-- Left Column (Detail & Deskripsi) -->
    <div class="card" style="grid-column: span 2; display: flex; flex-direction: column; gap: 20px;">
        <div class="card-header" style="border-bottom: 1px solid #eee; padding-bottom: 12px; margin-bottom: 0;">
            <div class="card-title" style="font-size: 1.1rem; font-weight: 700; color: var(--charcoal);">
                {{ $ticket->title }}
            </div>
        </div>

        <!-- Metadata Info Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; background: var(--cream); padding: 16px; border-radius: var(--radius-sm);">
            @if(auth()->user()->isSiswa() && $ticket->anonymous)
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; display: block; margin-bottom: 2px;">Siswa</label>
                    <div style="font-size: 14px; font-weight: 600; color: var(--charcoal);">
                        Anonim
                    </div>
                </div>
            @else
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; display: block; margin-bottom: 2px;">Siswa</label>
                    <div style="font-size: 14px; font-weight: 600; color: var(--charcoal);">
                        @if(auth()->user()->isSiswa())
                            {{ $ticket->anonymous ? 'Kamu (Anonim)' : $ticket->student?->user?->name ?? '-' }}
                        @else
                            {{ $ticket->student?->user?->name ?? '-' }}
                            @if($ticket->anonymous) <span class="badge badge-warning" style="font-size:9px;padding:1px 5px;margin-left:4px">Anonim</span> @endif
                        @endif
                    </div>
                </div>
                <div>
                    <label style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; display: block; margin-bottom: 2px;">Kelas</label>
                    <div style="font-size: 14px; font-weight: 600; color: var(--charcoal);">
                        {{ $ticket->student->class->name ?? '-' }}
                    </div>
                </div>
            @endif
            <div>
                <label style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; display: block; margin-bottom: 2px;">Layanan</label>
                <div>
                    @if($ticket->service)
                        <span class="badge" style="background: {{ $ticket->service->color ?? 'var(--teal)' }}; color: #fff; font-size: 10px; padding: 2px 8px; border-radius: 4px; font-weight: 600;">
                            <i class="fas {{ str_starts_with($ticket->service->icon ?? 'fa-tag', 'fas ') ? Str::after($ticket->service->icon, 'fas ') : ($ticket->service->icon ?? 'fa-tag') }}"></i> {{ $ticket->service->name }}
                        </span>
                    @else
                        <span style="font-size: 14px; font-weight: 600; color: var(--charcoal);">-</span>
                    @endif
                </div>
            </div>
            <div>
                <label style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; display: block; margin-bottom: 2px;">Guru BK</label>
                <div style="font-size: 14px; font-weight: 600; color: var(--charcoal);">
                    <i class="fas fa-chalkboard-teacher" style="color: var(--teal); font-size: 12px; margin-right: 4px;"></i>
                    {{ $ticket->teacher?->user?->name ?? 'Belum ditugaskan' }}
                </div>
            </div>
        </div>

        @if($ticket->status === 'dibatalkan')
            <div style="display: flex; align-items: flex-start; gap: 10px; background-color: #fef2f2; border: 1px solid #fecaca; color: #ef4444; padding: 12px 16px; border-radius: 6px; font-size: 13px; font-weight: 500; text-align: left; margin-bottom: 20px;">
                <i class="fas fa-ban" style="font-size: 16px; margin-top: 2px; flex-shrink: 0;"></i>
                <div>
                    <strong style="color: #dc2626; display: block; margin-bottom: 4px; font-size: 14px;">Konsultasi ini Dibatalkan</strong>
                    {{ $ticket->cancel_reason ?? 'Konsultasi ini telah dibatalkan.' }}
                </div>
            </div>
        @endif

        @if($ticket->anonymous && auth()->user()->isSiswa())
            <div style="display: flex; align-items: center; gap: 8px; background-color: #1e293b; border: 1px solid #334155; color: #94a3b8; padding: 10px 14px; border-radius: 6px; font-size: 12px; font-weight: 500;">
                <i class="fas fa-user-secret" style="font-size: 14px; color: #cbd5e1;"></i>
                <span style="color: #cbd5e1;"><strong style="color: #e2e8f0;">Konsultasi Anonim:</strong> Kamu mengajukan konsultasi ini secara anonim. Identitasmu sepenuhnya tersembunyi dan terjaga kerahasiaannya di sistem.</span>
            </div>
        @endif

        <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; display: block; margin-bottom: 6px; letter-spacing: 0.3px;">Deskripsi Masalah</label>
            <div style="line-height: 1.6; color: var(--slate); font-size: 14px; white-space: pre-wrap; background: #fafafa; border-left: 4px solid var(--teal); padding: 16px; border-radius: 0 8px 8px 0; word-break: break-word;">{{ $ticket->description }}</div>
        </div>
    </div>

    <!-- Right Column (Aksi) -->
    <div class="card" style="display: flex; flex-direction: column; gap: 16px; height: fit-content;">
        <div class="card-header" style="margin-bottom: 0; padding-bottom: 12px; border-bottom: 1px solid #eee;"><div class="card-title">Tindakan Tiket</div></div>
        <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 16px;">
            <a href="{{ route('chat.show', $ticket) }}" class="btn btn-primary" style="justify-content: center; padding: 12px; font-size: 14px;"><i class="fas fa-comments"></i> Buka Chat Konsultasi</a>
            
            @if(auth()->user()->isAdmin() && !$ticket->teacher_id)
            <form method="POST" action="{{ route('tickets.assign', $ticket) }}" style="border-top: 1px solid #eee; padding-top: 16px; margin-top: 8px;">
                @csrf
                <div class="field-group" style="margin-bottom: 12px;">
                    <label style="font-weight: 600; font-size: 13px; color: var(--slate);">Tugaskan Guru BK</label>
                    <select name="teacher_id" required style="width: 100%; box-sizing: border-box;">
                        @foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->user->name }}</option>@endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-gold" style="width: 100%; justify-content: center; padding: 10px;"><i class="fas fa-user-check"></i> Tugaskan Guru</button>
            </form>
            @endif
            
            @if(auth()->user()->isGuru() && $ticket->status !== 'selesai')
            <form method="POST" action="{{ route('tickets.status', $ticket) }}" style="border-top: 1px solid #eee; padding-top: 16px; margin-top: 8px;">
                @csrf <input type="hidden" name="status" value="selesai">
                <button type="submit" class="btn btn-gold" style="width: 100%; justify-content: center; padding: 10px;" onclick="return confirm('Selesaikan tiket konsultasi ini?')"><i class="fas fa-check-double"></i> Selesaikan Konsultasi</button>
            </form>
            @endif
        </div>
    </div>
</div>

@if($ticket->counselingNote)
<div class="card mt-20" style="display: flex; flex-direction: column; gap: 16px;">
    <div class="card-header" style="margin-bottom: 0; padding-bottom: 12px; border-bottom: 1px solid #eee; display: flex; align-items: center; justify-content: space-between;">
        <div class="card-title" style="font-size: 1.05rem; font-weight: 700; color: var(--charcoal);"><i class="fas fa-clipboard-list" style="color: var(--teal); margin-right: 6px;"></i> Catatan Hasil Konseling</div>
        <a href="{{ route('catatan.pdf', $ticket->counselingNote) }}" class="btn btn-secondary btn-sm" style="margin: 0;"><i class="fas fa-file-pdf"></i> Unduh Laporan PDF</a>
    </div>
    <div style="display: flex; flex-direction: column; gap: 14px; margin-top: 8px; text-align: left;">
        <div>
            <label style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase;">Masalah / Keluhan</label>
            <p style="font-size: 14px; color: var(--charcoal); line-height: 1.5; margin-top: 2px;">{{ $ticket->counselingNote->masalah }}</p>
        </div>
        <div>
            <label style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase;">Tindakan / Solusi yang Diberikan</label>
            <p style="font-size: 14px; color: var(--charcoal); line-height: 1.5; margin-top: 2px;">{{ $ticket->counselingNote->tindakan }}</p>
        </div>
    </div>
</div>
@endif
@endsection
