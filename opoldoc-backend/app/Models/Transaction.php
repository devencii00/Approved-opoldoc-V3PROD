<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'transactions';

    protected $primaryKey = 'transaction_id';

    protected $fillable = [
        'appointment_id',
        'amount',
        'discount_amount',
        'discount_type',
        'payment_mode',
        'payment_status',
        'reference_number',
        'receipt_path',
        'transaction_datetime',
        'visit_datetime',
        'diagnosis',
        'treatment_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'transaction_datetime' => 'datetime',
        'visit_datetime' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'transaction_id', 'transaction_id');
    }
}
