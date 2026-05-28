<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jurnal Kegiatan BK</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; margin: 30px; }
        .header { text-align: center; border-bottom: 2px solid #0d7c66; padding-bottom: 12px; margin-bottom: 20px; }
        h1 { font-size: 18px; color: #0d7c66; margin: 0 0 5px 0; }
        h2 { font-size: 14px; color: #333; margin: 0 0 10px 0; }
        .meta-info { width: 100%; margin-bottom: 20px; }
        .meta-info td { padding: 4px; vertical-align: top; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
        .table th { background: #f7f3ee; color: #0d7c66; font-weight: bold; }
        .footer { margin-top: 40px; text-align: right; font-size: 10px; color: #777; }
        .signature { margin-top: 50px; float: right; width: 200px; text-align: center; }
        .signature .line { border-top: 1px solid #333; margin-top: 50px; padding-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>JURNAL KEGIATAN BIMBINGAN KONSELING</h1>
        <h2>Sistem E-BK — SMK Negeri 1 Contoh</h2>
    </div>

    <table class="meta-info">
        <tr>
            <td width="120"><strong>Filter Guru BK</strong></td><td>: {{ $teacher ? $teacher->user->name : 'Semua Guru BK' }}</td>
            <td width="100"><strong>Bulan</strong></td><td>: {{ $month }}</td>
        </tr>
        <tr>
            <td><strong>Total Kegiatan</strong></td><td>: {{ $journals->count() }}</td>
            <td></td><td></td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th width="30">No</th>
                <th width="80">Tanggal</th>
                <th width="100">Siswa</th>
                <th width="100">Guru BK</th>
                <th>Catatan (Masalah & Tindakan)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($journals as $i => $j)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $j->created_at->format('d/m/Y') }}<br><small>{{ $j->created_at->format('H:i') }}</small></td>
                <td>{{ $j->ticket->student->user->name }}</td>
                <td>{{ $j->counselingNote->teacher->user->name ?? '-' }}</td>
                <td>
                    <strong>{{ $j->counselingNote->title ?? 'Tanpa Judul' }}</strong><br>
                    <em>Masalah:</em> {!! nl2br(e($j->counselingNote->masalah ?? '-')) !!}<br>
                    <em>Tindakan:</em> {!! nl2br(e($j->counselingNote->tindakan ?? '-')) !!}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center">Belum ada data jurnal kegiatan untuk filter yang dipilih.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature">
        <p>Mengetahui,<br>Kepala Sekolah / Koordinator BK</p>
        <div class="line">
            <strong>_____________________</strong><br>
            NIP: 
        </div>
    </div>
    <div style="clear:both"></div>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
