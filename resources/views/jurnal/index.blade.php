@extends('layouts.app')
@section('title', 'Jurnal Kegiatan BK')
@section('page-title', 'Jurnal Kegiatan BK')

@section('content')
<div class="page-header-row">
    <div class="page-header">
        <h2>Jurnal Kegiatan BK</h2>
        <p>Akumulasi catatan dan dokumentasi konseling dari seluruh Guru BK</p>
    </div>
</div>

<form class="filter-bar" method="GET" action="{{ route('jurnal.index') }}">
    <input type="month" name="month" value="{{ request('month') }}">
    <select name="teacher_id" onchange="this.form.submit()">
        <option value="">Semua Guru BK</option>
        @foreach($teachers as $t)
            <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->user->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
    <button type="submit" formaction="{{ route('jurnal.rekap') }}" class="btn btn-primary" style="margin-left:auto"><i class="fas fa-file-pdf"></i> Cetak Jurnal PDF</button>
</form>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Tiket / Siswa</th>
                    <th>Masalah & Tindakan</th>
                    <th>Guru Pengampu</th>
                    <th style="width:100px;text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($journals as $j)
                <tr>
                    <td>
                        {{ $j->created_at->format('d M Y') }}<br>
                        <small class="text-muted">{{ $j->created_at->format('H:i') }}</small>
                    </td>
                    <td>
                        <strong>{{ $j->ticket->code }}</strong><br>
                        {{ $j->ticket->student_name ?? $j->ticket->student->user->name }}
                    </td>
                    <td>
                        <div style="font-weight:600;margin-bottom:4px">{{ $j->counselingNote->title ?? 'Tanpa Judul' }}</div>
                        <div style="font-size:12px;color:var(--slate)">
                            <strong>Masalah:</strong> {{ Str::limit($j->counselingNote->masalah ?? '-', 50) }}<br>
                            <strong>Tindakan:</strong> {{ Str::limit($j->counselingNote->tindakan ?? '-', 50) }}
                        </div>
                    </td>
                    <td>
                        {{ $j->counselingNote->teacher->user->name ?? '-' }}
                    </td>
                    <td style="text-align:center">
                        <a href="{{ route('chat.show', $j->ticket_id) }}" class="btn btn-secondary btn-sm" title="Lihat Dokumentasi Chat"><i class="fas fa-comments"></i> Chat</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:24px" class="text-muted">
                        <i class="fas fa-book-open" style="font-size:32px;margin-bottom:8px;color:var(--cream-dark)"></i>
                        <p>Belum ada data jurnal kegiatan.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $journals->links('vendor.pagination.custom') }}
@endsection
