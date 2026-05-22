<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CounselingNote extends Model
{
    use HasFactory;

    protected $fillable = ['ticket_id', 'teacher_id', 'title', 'masalah', 'tindakan', 'kesimpulan'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function journal()
    {
        return $this->hasOne(Journal::class);
    }
}
