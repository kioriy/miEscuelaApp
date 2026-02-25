<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherGroupAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'grade',
        'group_letter',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
