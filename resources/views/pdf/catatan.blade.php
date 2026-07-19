<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Catatan Konseling - {{ $note->ticket->code }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 2cm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.5;
            color: #000;
            margin: 30px;
        }

        .kop-surat {
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        .kop-surat h2 {
            margin: 0 0 4px;
            font-size: 15px;
            text-transform: uppercase;
            text-align: center;
        }

        .kop-surat p {
            margin: 2px 0;
            font-size: 10px;
            text-align: center;
        }

        .judul {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
            text-decoration: underline;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .info-table .label {
            font-weight: bold;
            width: 140px;
        }

        .section-box {
            border: 1px solid #000;
            padding: 10px 14px;
            margin-bottom: 15px;
            border-radius: 4px;
            page-break-inside: avoid;
        }

        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }

        .section-content {
            white-space: pre-wrap;
            line-height: 1.5;
            text-align: justify;
        }

        .signature-container {
            margin-top: 30px;
            width: 100%;
            display: table;
            page-break-inside: avoid;
        }

        .sig-left {
            display: table-cell;
            width: 60%;
        }

        .sig-right {
            display: table-cell;
            width: 40%;
            text-align: center;
        }

        .sig-space {
            height: 60px;
        }
    </style>
</head>
<body>
    @php
        \Carbon\Carbon::setLocale('id');
        $location = 'Malang'; // default fallback
        if ($institute && $institute->address) {
            $parts = explode(',', $institute->address);
            if (count($parts) > 1) {
                $locPart = trim($parts[count($parts) - 2]);
                $location = preg_replace('/^(Kota|Kab\.|Kabupaten|Kec\.)\s+/i', '', $locPart);
            }
        }
    @endphp

    <div class="kop-surat">
        <h2>{{ $institute->name ?? 'SMKN 2 SINGOSARI' }}</h2>
        <p>NPSN: {{ $institute->npsn ?? '20566286' }}</p>
        <p>{{ $institute->address ?? 'JL. PERUSAHAAN NO.20 TUNJUNGTIRTO-SINGOSARI, Kab. Malang, Prov. Jawa Timur' }}</p>
        <p>Telp. {{ $institute->phone ?? '03414345127' }} | Email: {{ $institute->email ?? 'smkn2.singosari@yahoo.co.id' }}</p>
    </div>

    <div class="judul">
        LAPORAN INDIVIDU LAYANAN BIMBINGAN DAN KONSELING
    </div>

    <table class="info-table">
        <tr>
            <td class="label" style="width: 22%;">Kode Tiket</td>
            <td style="width: 3%;">:</td>
            <td style="width: 75%;">{{ $note->ticket->code }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Konseling</td>
            <td>:</td>
            <td>{{ $note->created_at->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label">Kategori Layanan</td>
            <td>:</td>
            <td>{{ $note->ticket->service->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nama Siswa</td>
            <td>:</td>
            <td>{{ $note->ticket->anonymous ? 'Anonim' : ($note->ticket->student->user->name ?? '-') }}</td>
        </tr>
        <tr>
            <td class="label">Kelas</td>
            <td>:</td>
            <td>{{ $note->ticket->class->name ?? $note->ticket->student->class->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Guru Pembimbing</td>
            <td>:</td>
            <td>{{ $note->teacher->user->name ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-box">
        <div class="section-title">Topik Konsultasi</div>
        <div class="section-content">{{ $note->title }}</div>
    </div>

    <div class="section-box">
        <div class="section-title">Permasalahan</div>
        <div class="section-content">{{ $note->masalah }}</div>
    </div>

    <div class="section-box">
        <div class="section-title">Tindakan / Solusi</div>
        <div class="section-content">{{ $note->tindakan }}</div>
    </div>

    @if($note->kesimpulan)
        <div class="section-box">
            <div class="section-title">Kesimpulan</div>
            <div class="section-content">{{ $note->kesimpulan }}</div>
        </div>
    @endif

    <table style="width: 100%; margin-top: 40px; border: none !important; page-break-inside: avoid;">
        <tr style="border: none !important;">
            <td style="width: 60%; border: none !important;"></td>
            <td style="width: 40%; text-align: center; border: none !important; font-family: 'Times New Roman', serif;">
                <p style="margin: 0;">{{ $location }}, {{ $note->created_at->translatedFormat('d F Y') }}</p>
                <p style="margin: 4px 0 0; font-weight: bold;">Guru Bimbingan Konseling</p>
                <div style="height: 60px;"></div>
                <p style="margin: 0;"><u>{{ $note->teacher->user->name ?? '........................' }}</u></p>
                @if($note->teacher && $note->teacher->nip)
                    <p style="margin: 2px 0 0;">NIP. {{ $note->teacher->nip }}</p>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
