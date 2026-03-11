<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function targets()
    {
        return $this->hasMany(AnnouncementTarget::class);
    }
}
