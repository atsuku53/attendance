<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Attendance;
use App\Models\Administrator;
use App\Models\ModifiedRest;

class ModifiedAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'administrator_id',
        'attend_start',
        'attend_end',
        'comment',
    ];

    protected $casts = [
        'attend_start' => 'datetime',
        'attend_end'   => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    public function administrator()
    {
        return $this->belongsTo(Administrator::class, 'administrator_id');
    }

    public function modifiedRests()
    {
        return $this->hasMany(ModifiedRest::class, 'modified_attendance_id');
    }

}
