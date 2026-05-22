@extends('layouts.app')
@section('title', 'Rekap Laporan')
@section('page-title', 'Rekap Laporan')

@section('content')
<div class="page-header">
    <h2>Rekap Laporan</h2>
    <p>Statistik dan laporan konseling komprehensif</p>
</div>

<div class="rekap-nav">
    <div class="rekap-tab active" onclick="switchRekap(this,'rekap-siswa')">Per Siswa</div>
    <div class="rekap-tab" onclick="switchRekap(this,'rekap-kelas')">Per Kelas</div>
    <div class="rekap-tab" onclick="switchRekap(this,'rekap-bulan')">Per Bulan</div>
</div>

{{-- Per Siswa --}}
<div id="rekap-siswa">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Rekap Per Siswa</div>
            <button class="btn btn-secondary btn-sm"><i class="fas fa-download"></i> Export</button>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nama Siswa</th><th>Kelas</th><th>Total Kasus</th><th>Selesai</th><th>Kategori Terbanyak</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($perSiswa as $row)
                    <tr>
                        <td>{{ $row['name'] }}</td>
                        <td>{{ $row['kelas'] }}</td>
                        <td>{{ $row['total'] }}</td>
                        <td>{{ $row['selesai'] }}</td>
                        <td>{{ $row['kategori'] }}</td>
                        <td><span class="badge {{ $row['status']==='Aktif' ? 'badge-warning' : 'badge-success' }}">{{ $row['status'] }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--muted)">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Per Kelas --}}
<div id="rekap-kelas" style="display:none">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Rekap Per Kelas</div>
            <button class="btn btn-secondary btn-sm"><i class="fas fa-download"></i> Export</button>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Kelas</th><th>Wali Kelas</th><th>Total Siswa</th><th>Pernah Konsultasi</th><th>Total Kasus</th><th>%</th></tr></thead>
                <tbody>
                    @forelse($perKelas as $row)
                    <tr>
                        <td>{{ $row['name'] }}</td>
                        <td>{{ $row['wali_kelas'] }}</td>
                        <td>{{ $row['total_siswa'] }}</td>
                        <td>{{ $row['pernah'] }}</td>
                        <td>{{ $row['total_kasus'] }}</td>
                        <td>{{ $row['persen'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--muted)">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Per Bulan --}}
<div id="rekap-bulan" style="display:none">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Rekap Per Bulan</div>
            <button class="btn btn-secondary btn-sm"><i class="fas fa-download"></i> Export</button>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Bulan</th><th>Tiket Masuk</th><th>Diselesaikan</th></tr></thead>
                <tbody>
                    @forelse($perBulan as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row->bulan.'-01')->isoFormat('MMMM YYYY') }}</td>
                        <td>{{ $row->total }}</td>
                        <td>{{ $row->selesai }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center;padding:24px;color:var(--muted)">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function switchRekap(tab, id) {
    document.querySelectorAll('.rekap-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    ['rekap-siswa','rekap-kelas','rekap-bulan'].forEach(r => {
        const el = document.getElementById(r);
        if (el) el.style.display = r === id ? 'block' : 'none';
    });
}
</script>
@endpush
