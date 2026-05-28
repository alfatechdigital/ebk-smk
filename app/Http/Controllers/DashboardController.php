<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $stats = [
            'total_siswa' => 0,
            'total_layanan' => \App\Models\Service::count(),
            'total_konsultasi' => 0,
            'menunggu' => 0,
            'diproses' => 0,
        ];

        $recentTickets = collect();
        $assignedGuru = null;
        $classActivities = collect();

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            $stats['total_siswa'] = Student::count();
            $stats['total_konsultasi'] = Ticket::count();
            $stats['menunggu'] = Ticket::where('status', 'menunggu')->count();
            $stats['diproses'] = Ticket::where('status', 'diproses')->count();
            $recentTickets = Ticket::with(['student.user', 'service'])->latest()->take(5)->get();
        } elseif ($user->isGuru()) {
            $teacherId = $user->teacher->id;
            // Total siswa in classes mentored by this Guru
            $stats['total_siswa'] = Student::whereHas('class', function($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            })->count();
            $stats['total_konsultasi'] = Ticket::where('teacher_id', $teacherId)->count();
            $stats['menunggu'] = Ticket::where('teacher_id', $teacherId)->where('status', 'menunggu')->count();
            $stats['diproses'] = Ticket::where('teacher_id', $teacherId)->where('status', 'diproses')->count();
            $recentTickets = Ticket::with(['student.user', 'service'])->where('teacher_id', $teacherId)->latest()->take(5)->get();

            // Activity per class mentored by this Guru
            $classes = SchoolClass::where('teacher_id', $teacherId)->get();
            foreach ($classes as $cls) {
                $count = Ticket::whereHas('student', function($q) use ($cls) {
                    $q->where('class_id', $cls->id);
                })->count();
                $classActivities->push(['class_name' => $cls->name, 'count' => $count]);
            }
        } elseif ($user->isSiswa()) {
            $studentId = $user->student->id;
            $stats['total_konsultasi'] = Ticket::where('student_id', $studentId)->count();
            $stats['menunggu'] = Ticket::where('student_id', $studentId)->where('status', 'menunggu')->count();
            $stats['diproses'] = Ticket::where('student_id', $studentId)->where('status', 'diproses')->count();
            $recentTickets = Ticket::with(['teacher.user', 'service'])->where('student_id', $studentId)->latest()->take(5)->get();
            $assignedGuru = $user->student->class->teacher->user->name ?? 'Belum ditugaskan';
        }

        $activities = $this->getActivities($user);

        return view('dashboard', compact('stats', 'recentTickets', 'assignedGuru', 'classActivities', 'activities'));
    }

    private function getActivities(User $user): array
    {
        // Latest ticket messages as activity log
        $tickets = Ticket::with(['student.user', 'teacher.user', 'service'])
            ->latest()->limit(4)->get();

        return $tickets->map(fn($t) => [
            'icon'  => 'fas fa-ticket-alt',
            'color' => 'teal',
            'text'  => 'Tiket ' . $t->code . ' - ' . $t->title,
            'time'  => $t->created_at->diffForHumans(),
        ])->toArray();
    }
}
