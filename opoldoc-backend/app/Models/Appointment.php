<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'appointments';

    protected $primaryKey = 'appointment_id';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'created_by',
        'appointment_datetime',
        'appointment_type',
        'status',
        'reason_for_visit',
        'priority_level',
        'check_in_time',
    ];

    protected $casts = [
        'appointment_datetime' => 'datetime',
        'check_in_time' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id', 'user_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id', 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function queue()
    {
        return $this->hasOne(Queue::class, 'appointment_id', 'appointment_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'appointment_id', 'appointment_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'appointment_services', 'appointment_id', 'service_id');
    }
}
