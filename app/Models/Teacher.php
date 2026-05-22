<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'institute_id', 'nip', 'spesialisasi', 'no_whatsapp', 'foto_profil'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function counselingNotes()
    {
        return $this->hasMany(CounselingNote::class);
    }

    public function getNameAttribute(): string
    {
        return $this->user->name ?? '';
    }

    public function getAvatarInitialsAttribute(): string
    {
        return $this->user->avatar_initials ?? 'GB';
    }
}
