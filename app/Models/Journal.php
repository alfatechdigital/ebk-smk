<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = ['ticket_id', 'counseling_note_id', 'pdf_path'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function counselingNote()
    {
        return $this->belongsTo(CounselingNote::class);
    }

    public function getPdfUrlAttribute(): ?string
    {
        return $this->pdf_path ? asset('storage/' . $this->pdf_path) : null;
    }
}
