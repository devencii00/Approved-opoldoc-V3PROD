<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientVerification extends Model
{
    use HasFactory;

    protected $table = 'patient_verifications';

    protected $primaryKey = 'verification_id';

    protected $fillable = [
        'patient_id',
        'type',
        'status',
        'document_path',
        'remarks',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id', 'user_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by', 'user_id');
    }
}
