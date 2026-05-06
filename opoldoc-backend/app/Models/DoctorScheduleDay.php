<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorScheduleDay extends Model
{
    use HasFactory;

    protected $table = 'doctor_schedule_days';

    public $timestamps = false;

    protected $fillable = [
        'schedule_id',
        'day_of_week',
    ];

    public function schedule()
    {
        return $this->belongsTo(DoctorSchedule::class, 'schedule_id');
    }
}
