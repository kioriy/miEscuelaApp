<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Kiosk extends Model
{
    use HasFactory, HasApiTokens;

    protected $guarded = [];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
