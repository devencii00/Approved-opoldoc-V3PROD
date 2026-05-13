<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    protected $appends = [
        'document_url',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id', 'user_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by', 'user_id');
    }

    public function getDocumentUrlAttribute(): ?string
    {
        $path = $this->document_path ?? null;
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $normalized = str_starts_with($path, 'storage/')
            ? substr($path, strlen('storage/'))
            : $path;

        if (! Storage::disk('public')->exists($normalized)) {
            return null;
        }

        return url('/api/patient-verifications/'.$this->verification_id.'/document');
    }
}
