<!DOCTYPE html>
<html>

<head>
    <title>Rekap Catatan Konseling</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.5cm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
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
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        .signature-section {
            margin-top: 35px;
            width: 100%;
            display: table;
            page-break-inside: avoid;
        }

        .signature-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
            padding-top: 8px;
        }

        .signature-right {
            display: table-cell;
            width: 40%;
            text-align: center;
            vertical-align: top;
        }

        .signature-space {
            height: 55px;
            margin: 10px auto 8px;
            width: 70%;
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
        REKAP CATATAN LAYANAN BIMBINGAN DAN KONSELING<br>
        <span style="font-size: 11px; font-weight: normal;">Periode: {{ $month }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 7%;">Tanggal</th>
                <th style="width: 15%;">Nama Siswa / Kelas</th>
                <th style="width: 12%;">Kategori Layanan</th>
                <th style="width: 27%;">Topik & Permasalahan</th>
                <th style="width: 36%;">Tindakan & Kesimpulan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notes as $index => $note)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center;">{{ $note->created_at->translatedFormat('d M Y') }}</td>
                    <td>
                        {{ $note->ticket->student->user->name ?? 'Anonim' }}<br>
                        {{ $note->ticket->student->class->name ?? '-' }}
                    </td>
                    <td>{{ $note->ticket->service->name ?? '-' }}</td>
                    <td>
                        <strong>{{ $note->title }}</strong><br>
                        <div style="margin-top: 4px; color: #333;">{{ $note->masalah }}</div>
                    </td>
                    <td>
                        <strong>Tindakan / solusi:</strong><br>
                        <div style="margin-bottom: 8px; color: #333;">{{ $note->tindakan }}</div>
                        <strong>Kesimpulan:</strong><br>
                        <div style="color: #333;">{{ $note->kesimpulan ?? '-' }}</div>
                    </td>
                </tr>
            @endforeach
            @if($notes->isEmpty())
                <tr>
                    <td colspan="6" style="text-align: center; padding: 15px; color: #666;">Tidak ada data catatan konseling.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 40px; border: none !important; page-break-inside: avoid;">
        <tr style="border: none !important;">
            <td style="width: 60%; border: none !important;"></td>
            <td style="width: 40%; text-align: center; border: none !important; font-family: 'Times New Roman', serif;">
                <p style="margin: 0;">{{ $location }}, {{ now()->translatedFormat('d F Y') }}</p>
                <p style="margin: 4px 0 0; font-weight: bold;">Guru Bimbingan Konseling</p>
                <div style="height: 55px;"></div>
                <p style="margin: 0;"><u>{{ $teacher->user->name ?? ($notes->first()?->teacher?->user?->name ?? '........................') }}</u></p>
                @if($teacher && $teacher->nip)
                    <p style="margin: 2px 0 0;">NIP. {{ $teacher->nip }}</p>
                @elseif($notes->first() && $notes->first()->teacher && $notes->first()->teacher->nip)
                    <p style="margin: 2px 0 0;">NIP. {{ $notes->first()->teacher->nip }}</p>
                @endif
            </td>
        </tr>
    </table>
</body>

</html>
