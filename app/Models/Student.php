<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'class_id', 'nis', 'no_hp', 'foto_profil'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function getNameAttribute(): string
    {
        return $this->user->name ?? '';
    }

    public function getAvatarInitialsAttribute(): string
    {
        return $this->user->avatar_initials ?? 'SS';
    }
}
