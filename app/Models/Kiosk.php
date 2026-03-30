<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Kiosk extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $hidden = [];
    protected $guarded = [];
    protected $appends = ['status'];
    protected $rememberTokenName = '';

    public function getStatusAttribute()
    {
        if (!$this->is_active) {
            return 'Inactivo';
        }

        if (!$this->last_sync_at) {
            return 'Offline';
        }

        // Si su última sincronización fue hace menos de 10 minutos
        $lastSync = \Illuminate\Support\Carbon::parse($this->last_sync_at);
        if ($lastSync->diffInMinutes(now()) <= 10) {
            return 'Activo';
        }

        return 'Offline';
    }

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
