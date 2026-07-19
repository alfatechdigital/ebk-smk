<?php

namespace App\Exports;

use App\Models\CounselingNote;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JurnalExport implements FromCollection, WithHeadings, WithMapping
{
    protected $teacher_id;
    protected $month;

    public function __construct($teacher_id = null, $month = null)
    {
        $this->teacher_id = $teacher_id;
        $this->month = $month;
    }

    public function collection()
    {
        $query = CounselingNote::with(['ticket.student.user', 'ticket.student.class', 'teacher.user', 'ticket.service'])
            ->latest();

        if ($this->teacher_id) {
            $query->where('teacher_id', $this->teacher_id);
        }

        if ($this->month) {
            $query->whereMonth('created_at', date('m', strtotime($this->month)))
                  ->whereYear('created_at', date('Y', strtotime($this->month)));
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Nama Siswa',
            'Kelas',
            'Nama Guru BK',
            'Judul/Akar Masalah',
            'Tindakan/Solusi',
            'Kesimpulan'
        ];
    }

    public function map($note): array
    {
        static $no = 1;
        return [
            $no++,
            $note->created_at->format('d/m/Y'),
            $note->ticket->student->user->name ?? '-',
            $note->ticket->class->name ?? $note->ticket->student->class->name ?? '-',
            $note->teacher->user->name ?? '-',
            $note->title . "\n" . $note->masalah,
            $note->tindakan,
            $note->kesimpulan
        ];
    }
}
