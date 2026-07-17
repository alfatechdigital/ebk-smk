<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'student_id', 'service_id', 'teacher_id',
        'status', 'is_favorite', 'priority', 'title', 'description',
        'prior_action', 'anonymous', 'scheduled_at', 'completed_at'
    ];

    protected $casts = [
        'anonymous' => 'boolean',
        'is_favorite' => 'boolean',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($ticket) {
            if (empty($ticket->code)) {
                $latest = static::latest('id')->first();
                $num = $latest ? ($latest->id + 1) : 1;
                $ticket->code = '#TK-' . date('Y') . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at');
    }

    public function counselingNote()
    {
        return $this->hasOne(CounselingNote::class);
    }

    public function journal()
    {
        return $this->hasOne(Journal::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'menunggu' => 'Menunggu',
            'diproses' => 'Diproses',
            'selesai'  => 'Selesai',
            default    => $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'menunggu' => 'badge-warning',
            'diproses' => 'badge-info',
            'selesai'  => 'badge-success',
            default    => 'badge-info',
        };
    }

    public function getUnreadCountForUser(User $user): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();
    }
}
