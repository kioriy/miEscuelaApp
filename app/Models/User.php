<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'google_id',
        'school_id',
        'profile_photo_path',
        'avatar_url',
        'is_active',
        'enrollment_code'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Many-to-Many relationship with schools.
     */
    public function schools(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(School::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Helper to check if the user has access to a specific school.
     */
    public function hasAccessToSchool($schoolId): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }
        return $this->schools->contains('id', $schoolId);
    }

    /**
     * Get the groups assigned to this user (if role is teacher).
     */
    public function teacherGroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TeacherGroupAssignment::class);
    }
}
