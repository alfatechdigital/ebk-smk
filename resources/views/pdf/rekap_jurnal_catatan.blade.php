<!DOCTYPE html>
<html>
<head>
    <title>Jurnal Kegiatan BK</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; }
        .header p { margin: 5px 0; color: #555; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Jurnal Kegiatan Bimbingan & Konseling</h2>
        <p>Bulan: {{ $month }} | Guru BK: {{ $teacher ? $teacher->user->name : 'Semua Guru' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Guru BK</th>
                <th>Judul / Masalah</th>
                <th>Tindakan / Solusi</th>
                <th>Kesimpulan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notes as $index => $note)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $note->created_at->format('d/m/Y') }}</td>
                <td>{{ $note->ticket->student->user->name ?? '-' }}</td>
                <td>{{ $note->ticket->student->class->name ?? '-' }}</td>
                <td>{{ $note->teacher->user->name ?? '-' }}</td>
                <td>
                    <strong>{{ $note->title }}</strong><br>
                    {{ $note->masalah }}
                </td>
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

</body>
</html>
