<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        // Per siswa
        $perSiswa = Student::with(['user', 'class', 'tickets.service'])
            ->whereHas('tickets', fn($q) => $teacher ? $q->where('teacher_id', $teacher->id) : $q)
            ->get()
            ->map(fn($s) => [
                'name'     => $s->user->name,
                'kelas'    => $s->class->name ?? '-',
                'total'    => $s->tickets->count(),
                'selesai'  => $s->tickets->where('status','selesai')->count(),
                'kategori' => $s->tickets->sortByDesc(fn($t) => $t->service_id)->first()?->service?->name ?? '-',
                'status'   => $s->tickets->whereIn('status',['menunggu','diproses'])->count() > 0 ? 'Aktif' : 'Selesai',
            ]);

        // Per kelas
        $perKelas = SchoolClass::with(['students.tickets'])->get()->map(fn($k) => [
            'name'          => $k->name,
            'wali_kelas'    => $k->wali_kelas ?? '-',
            'total_siswa'   => $k->students->count(),
            'pernah'        => $k->students->filter(fn($s) => $s->tickets->count() > 0)->count(),
            'total_kasus'   => $k->students->sum(fn($s) => $s->tickets->count()),
            'persen'        => $k->students->count() > 0
                ? round($k->students->filter(fn($s) => $s->tickets->count() > 0)->count() / $k->students->count() * 100) . '%'
                : '0%',
        ]);

        // Per bulan
        $perBulan = Ticket::selectRaw("strftime('%Y-%m', created_at) as bulan, count(*) as total, sum(status='selesai') as selesai")
            ->when($teacher, fn($q) => $q->where('teacher_id', $teacher->id))
            ->groupBy('bulan')
            ->orderByDesc('bulan')
            ->limit(12)
            ->get();

        return view('rekap.index', compact('perSiswa', 'perKelas', 'perBulan'));
    }
}
