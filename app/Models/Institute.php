<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'address', 'phone', 'kepala_sekolah', 'kota_ttd', 'media_expiry_days'
    ];

    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }
}
