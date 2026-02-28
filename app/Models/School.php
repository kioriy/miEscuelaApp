<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function ownedKiosks()
    {
        return $this->hasMany(Kiosk::class, 'owner_school_id');
    }

    public function activeKiosks()
    {
        return $this->belongsToMany(Kiosk::class, 'kiosk_school')
            ->withPivot('created_at', 'updated_at')
            ->withTimestamps();
    }

    /**
     * Many-to-Many relationship with users.
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }
}
