<!DOCTYPE html>
<html>

<head>
    <title>Rekap Jurnal Kegiatan BK</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
        }

        .kop-surat {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .kop-surat h2 {
            margin: 0 0 4px;
            font-size: 16px;
            text-transform: uppercase;
        }

        .kop-surat p {
            margin: 2px 0;
            font-size: 11px;
        }

        .judul {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 12px;
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
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .signature-section {
            margin-top: 35px;
            width: 100%;
            display: table;
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
            height: 70px;
            border-bottom: 1px solid #000;
            margin: 10px auto 8px;
            width: 70%;
        }
    </style>
</head>

<body>
    <div class="kop-surat">
        <h2>{{ $institute->name ?? 'SMKN 2 SINGOSARI' }}</h2>
        <p>{{ $institute->address ?? 'JL. PERUSAHAAN NO.20 TUNJUNGTIRTO-SINGOSARI
TUNJUNGTIRTO, Kec. Singosari
Kab. Malang, Prov. Jawa Timur
Kode Pos: 65153' }}</p>
        <p>Telp. {{ $institute->phone ?? '03414345127' }}</p>
    </div>

    <div class="judul">REKAP JURNAL KEGIATAN BIMBINGAN & KONSELING</div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Guru BK</th>
                <th>Masalah</th>
                <th>Tindakan / Solusi</th>
                <th>Kesimpulan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notes as $index => $note)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $note->created_at->format('d/m/Y') }}</td>
                    <td>{{ $note->ticket->student_name ?? $note->ticket->student->user->name ?? '-' }}</td>
                    <td>{{ $note->ticket->class_name ?? $note->ticket->class->name ?? $note->ticket->student->class->name ?? '-' }}</td>
                    <td>{{ $note->teacher->user->name ?? '-' }}</td>
                    <td><strong>{{ $note->title }}</strong><br>{{ $note->masalah }}</td>
                    <td>{{ $note->tindakan }}</td>
                    <td>{{ $note->kesimpulan ?? '-' }}</td>
                </tr>
            @endforeach
            @if($notes->isEmpty())
                <tr>
                    <td colspan="8" style="text-align: center;">Tidak ada data jurnal.</td>
                </tr>
            @endif
        </tbody>
    </table>

    @php
        $location = 'Malang';
        if (!empty($institute->kota_ttd)) {
            $location = $institute->kota_ttd;
        } elseif ($institute && $institute->address) {
            $parts = explode(',', $institute->address);
            if (count($parts) > 1) {
                $locPart = trim($parts[count($parts) - 2]);
                $location = preg_replace('/^(Kota|Kab\.|Kabupaten|Kec\.)\s+/i', '', $locPart);
            }
        }
    @endphp

    <div class="signature-section">
        <div class="signature-left">
            <p style="margin: 0;">{{ $location }}, {{ now()->translatedFormat('d F Y') }}</p>
        </div>
        <div class="signature-right">
            <p style="margin: 0;">Mengetahui,</p>
            <p style="margin: 4px 0 0;">Kepala Sekolah</p>
            <div class="signature-space"></div>
            <p style="margin: 0;"><u>{{ $institute->kepala_sekolah ?? '........................' }}</u></p>
            <p style="margin: 2px 0 0;">Budiono S.Pd., M.M</p>
        </div>
    </div>
</body>

</html>