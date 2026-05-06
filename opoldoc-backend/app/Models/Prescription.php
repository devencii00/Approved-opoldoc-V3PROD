<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'prescriptions';

    protected $primaryKey = 'prescription_id';

    protected $fillable = [
        'transaction_id',
        'doctor_id',
        'notes',
        'prescribed_datetime',
    ];

    protected $casts = [
        'prescribed_datetime' => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'transaction_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id', 'user_id');
    }

    public function items()
    {
        return $this->hasMany(PrescriptionItem::class, 'prescription_id', 'prescription_id');
    }
}
