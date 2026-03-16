<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'user_id',
        'scanned_at',
        'type',
        'status',
        'notes',
        'kiosk_id',
        'sync_status'
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function kiosk()
    {
        return $this->belongsTo(Kiosk::class);
    }
}
