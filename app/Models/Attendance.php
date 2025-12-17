<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'match',
        'distance',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'match' => 'boolean',
        'distance' => 'float',
        'date' => 'date',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
