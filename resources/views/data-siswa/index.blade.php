@extends('layouts.app')
@section('title', 'Data Siswa')
@section('page-title', 'Data Siswa')

@section('content')
<div class="page-header">
    <h2>Data Siswa</h2>
    <p>Daftar seluruh siswa yang terdaftar</p>
</div>

<form class="filter-bar" method="GET">
    <input type="text" name="search" placeholder="Cari nama siswa..." value="{{ request('search') }}">
    <select name="class_id" onchange="this.form.submit()">
        <option value="">Semua Kelas</option>
        @foreach($classes as $c)<option value="{{ $c->id }}" {{ request('class_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach
    </select>
</form>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead><tr><th>No</th><th>Nama</th><th>NIS</th><th>Kelas</th><th>Total Konsultasi</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($students as $i => $s)
                <tr>
                    <td>{{ $students->firstItem() + $i }}</td>
                    <td>{{ $s->user->name }}</td>
                    <td>{{ $s->nis ?? '-' }}</td>
                    <td>{{ $s->class?->name ?? '-' }}</td>
                    <td>{{ $s->tickets->count() }}</td>
                    <td><span class="badge {{ $s->user->is_active?'badge-success':'badge-danger' }}">{{ $s->user->is_active?'Aktif':'Nonaktif' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-muted" style="text-align:center">Belum ada data siswa</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $students->links() }}
</div>
@endsection
