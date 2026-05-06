<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    use HasFactory;

    protected $table = 'doctor_schedules';

    protected $primaryKey = 'schedule_id';

    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room_number',
        'max_patients',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'room_number' => 'integer',
        'max_patients' => 'integer',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id', 'user_id');
    }
}
