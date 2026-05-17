<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Rest;
use App\Models\ModifiedAttendance;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attend_start',
        'attend_end',
    ];

    protected $casts = [
        'attend_start' => 'datetime',
        'attend_end'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rests()
    {
        return $this->hasMany(Rest::class, 'attendance_id');
    }

    public function modifiedAttendances()
    {
        return $this->hasMany(ModifiedAttendance::class, 'attendance_id');
    }

}
