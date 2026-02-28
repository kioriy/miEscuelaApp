<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Kiosk extends Model
{
    use HasFactory, HasApiTokens;

    protected $guarded = [];

    public function ownerSchool()
    {
        return $this->belongsTo(School::class, 'owner_school_id');
    }

    public function schools()
    {
        return $this->belongsToMany(School::class, 'kiosk_school')
            ->withPivot('created_at', 'updated_at')
            ->withTimestamps();
    }
}
