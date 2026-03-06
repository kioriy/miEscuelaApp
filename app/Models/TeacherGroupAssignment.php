<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherGroupAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'classroom_id',
        'school_id',
        'school_level',
        'shift',
    ];

    const UPDATED_AT = null;

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}
