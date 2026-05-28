<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jurnal Konseling - {{ $note->ticket->code }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; margin: 40px; }
        h1 { font-size: 20px; color: #0d7c66; margin-bottom: 5px; }
        h2 { font-size: 16px; color: #333; margin-bottom: 20px; }
        .header { text-align: center; border-bottom: 2px solid #0d7c66; padding-bottom: 16px; margin-bottom: 24px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 6px 8px; vertical-align: top; }
        .info-table .label { font-weight: bold; width: 160px; color: #555; }
        .section { margin-bottom: 16px; }
        .section h3 { font-size: 14px; color: #0d7c66; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin-bottom: 8px; }
        .section p { line-height: 1.6; text-align: justify; }
        .footer { margin-top: 40px; text-align: right; font-size: 11px; color: #777; }
        .signature { margin-top: 60px; display: flex; justify-content: space-between; }
        .sig-box { text-align: center; width: 200px; display: inline-block; }
        .sig-box .line { border-top: 1px solid #333; margin-top: 60px; padding-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>JURNAL KONSELING</h1>
        <h2>Sistem E-BK — Bimbingan Konseling Online</h2>
    </div>

    <table class="info-table">
        <tr><td class="label">Kode Tiket</td><td>: {{ $note->ticket->code }}</td></tr>
        <tr><td class="label">Tanggal Konseling</td><td>: {{ $note->created_at->format('d F Y') }}</td></tr>
        <tr><td class="label">Nama Siswa</td><td>: {{ $note->ticket->anonymous ? '(Anonim)' : ($note->ticket->student?->user?->name ?? '-') }}</td></tr>
        <tr><td class="label">Kelas</td><td>: {{ $note->ticket->student?->class?->name ?? '-' }}</td></tr>
        <tr><td class="label">Guru BK</td><td>: {{ $note->teacher?->user?->name ?? '-' }}</td></tr>
        <tr><td class="label">Jenis Layanan</td><td>: {{ $note->ticket->service?->name ?? '-' }}</td></tr>
    </table>

    <div class="section">
        <h3>Judul / Topik</h3>
        <p>{{ $note->title }}</p>
    </div>

    <div class="section">
        <h3>Permasalahan</h3>
        <p>{!! nl2br(e($note->masalah)) !!}</p>
    </div>

    <div class="section">
        <h3>Tindakan yang Dilakukan</h3>
        <p>{!! nl2br(e($note->tindakan)) !!}</p>
    </div>

    @if($note->kesimpulan)
    <div class="section">
        <h3>Kesimpulan</h3>
        <p>{!! nl2br(e($note->kesimpulan)) !!}</p>
    </div>
    @endif

    <div style="margin-top:60px">
        <div style="float:right;text-align:center;width:200px">
            <p>{{ $note->created_at->format('d F Y') }}</p>
            <p style="margin-top:4px;font-weight:bold">Guru BK</p>
            <div style="margin-top:50px;border-top:1px solid #333;padding-top:4px">{{ $note->teacher?->user?->name ?? '-' }}</div>
            <p style="font-size:10px;color:#777">NIP: {{ $note->teacher?->nip ?? '-' }}</p>
        </div>
        <div style="clear:both"></div>
    </div>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }} — Dokumen ini bersifat rahasia</p>
    </div>
</body>
</html>
