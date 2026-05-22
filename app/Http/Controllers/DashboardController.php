<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $stats = match($user->role) {
            'superadmin' => $this->superadminStats(),
            'admin'      => $this->adminStats(),
            'guru'       => $this->guruStats($user),
            'siswa'      => $this->siswaStats($user),
            default      => [],
        };

        $recentTickets = $this->getRecentTickets($user);
        $activities    = $this->getActivities($user);

        return view('dashboard', compact('stats', 'recentTickets', 'activities'));
    }

    private function superadminStats(): array
    {
        return [
            ['icon' => 'fas fa-users',            'val' => User::count(),                            'label' => 'Total User',    'cls' => ''],
            ['icon' => 'fas fa-ticket-alt',        'val' => Ticket::count(),                          'label' => 'Total Tiket',   'cls' => 'gold'],
            ['icon' => 'fas fa-chalkboard-teacher','val' => User::where('role','guru')->count(),       'label' => 'Guru BK',       'cls' => 'slate'],
            ['icon' => 'fas fa-check-circle',      'val' => Ticket::where('status','selesai')->count(),'label' => 'Tiket Selesai', 'cls' => 'danger'],
        ];
    }

    private function adminStats(): array
    {
        return [
            ['icon' => 'fas fa-user-graduate', 'val' => Student::count(),                              'label' => 'Total Siswa', 'cls' => ''],
            ['icon' => 'fas fa-ticket-alt',    'val' => Ticket::count(),                               'label' => 'Total Tiket', 'cls' => 'gold'],
            ['icon' => 'fas fa-clock',         'val' => Ticket::where('status','menunggu')->count(),    'label' => 'Menunggu',    'cls' => 'danger'],
            ['icon' => 'fas fa-check-circle',  'val' => Ticket::where('status','selesai')->count(),     'label' => 'Selesai',     'cls' => 'slate'],
        ];
    }

    private function guruStats(User $user): array
    {
        $teacher = $user->teacher;
        $q = $teacher ? Ticket::where('teacher_id', $teacher->id) : Ticket::whereNull('id');
        return [
            ['icon' => 'fas fa-ticket-alt',  'val' => (clone $q)->whereIn('status',['menunggu','diproses'])->count(), 'label' => 'Tiket Aktif',        'cls' => ''],
            ['icon' => 'fas fa-clock',       'val' => (clone $q)->where('status','menunggu')->count(),                 'label' => 'Menunggu Balasan',    'cls' => 'gold'],
            ['icon' => 'fas fa-users',       'val' => $teacher ? Student::where('class_id', '!=', null)->count() : 0, 'label' => 'Siswa Dibimbing',     'cls' => 'slate'],
            ['icon' => 'fas fa-check-circle','val' => (clone $q)->where('status','selesai')->count(),                  'label' => 'Total Selesai',       'cls' => ''],
        ];
    }

    private function siswaStats(User $user): array
    {
        $student = $user->student;
        $q = $student ? Ticket::where('student_id', $student->id) : Ticket::whereNull('id');
        return [
            ['icon' => 'fas fa-ticket-alt',  'val' => (clone $q)->count(),                                    'label' => 'Tiket Saya',    'cls' => ''],
            ['icon' => 'fas fa-comments',    'val' => (clone $q)->whereIn('status',['diproses'])->count(),     'label' => 'Chat Aktif',    'cls' => 'gold'],
            ['icon' => 'fas fa-check-circle','val' => (clone $q)->where('status','selesai')->count(),           'label' => 'Selesai',       'cls' => 'slate'],
            ['icon' => 'fas fa-shield-alt',  'val' => '100%',                                                  'label' => 'Privasi Terjaga','cls' => ''],
        ];
    }

    private function getRecentTickets(User $user)
    {
        $q = Ticket::with(['student.user', 'teacher.user', 'service'])
            ->latest()->limit(5);

        if ($user->role === 'guru' && $user->teacher) {
            $q->where('teacher_id', $user->teacher->id);
        } elseif ($user->role === 'siswa' && $user->student) {
            $q->where('student_id', $user->student->id);
        }

        return $q->get();
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
