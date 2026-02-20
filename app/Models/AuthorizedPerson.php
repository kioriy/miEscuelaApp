<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorizedPerson extends Model
{
    use HasFactory;

    protected $table = 'authorized_persons';
    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
