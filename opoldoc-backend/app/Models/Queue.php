<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Queue extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'queues';

    protected $primaryKey = 'queue_id';

    protected $fillable = [
        'appointment_id',
        'queue_number',
        'queue_code',
        'queue_datetime',
        'status',
        'priority_level',
    ];

    protected $casts = [
        'queue_datetime' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $queue) {
            $priorityLevel = self::sanitizePriorityLevel($queue->priority_level);
            if ($priorityLevel === null && $queue->appointment_id) {
                $appointment = Appointment::query()->find((int) $queue->appointment_id, ['appointment_id', 'patient_id', 'priority_level']);
                if ($appointment) {
                    $priorityLevel = self::sanitizePriorityLevel($appointment->priority_level);
                    if ($priorityLevel === null) {
                        $priorityLevel = self::priorityLevelForPatientId((int) $appointment->patient_id);
                    }
                }
            }

            $queue->priority_level = $priorityLevel ?? 5;

            $queueCode = is_string($queue->queue_code) ? trim($queue->queue_code) : '';
            if ($queueCode === '') {
                $queue->queue_code = self::generateUniqueQueueCode((int) $queue->priority_level);
            }
        });

        static::updating(function (self $queue) {
            if (! $queue->isDirty('priority_level')) {
                return;
            }

            $queue->priority_level = self::sanitizePriorityLevel($queue->priority_level) ?? 5;

            $queueCode = is_string($queue->queue_code) ? trim($queue->queue_code) : '';
            $expectedPrefix = self::prefixForLevel((int) $queue->priority_level);
            $existingPrefix = strtoupper(trim(explode('-', $queueCode, 2)[0] ?? ''));

            if ($queueCode === '' || $existingPrefix !== $expectedPrefix) {
                $queue->queue_code = self::generateUniqueQueueCode((int) $queue->priority_level);
            }
        });
    }

    public static function sanitizePriorityLevel($value): ?int
    {
        if (is_string($value) && $value !== '' && is_numeric($value)) {
            $value = (int) $value;
        }
        if (! is_int($value)) {
            return null;
        }

        if ($value < 1) {
            $value = 1;
        }
        if ($value > 5) {
            $value = 5;
        }

        return $value;
    }

    public static function priorityLevelForPatientId(int $patientId): ?int
    {
        $approvedTypes = PatientVerification::query()
            ->where('patient_id', $patientId)
            ->where('status', 'approved')
            ->pluck('type')
            ->map(fn ($t) => is_string($t) ? strtolower(trim($t)) : '')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (in_array('pwd', $approvedTypes, true)) {
            return 2;
        }
        if (in_array('pregnant', $approvedTypes, true)) {
            return 3;
        }
        if (in_array('senior', $approvedTypes, true)) {
            return 4;
        }

        return 5;
    }

    public static function prefixForLevel(int $level): string
    {
        $level = self::sanitizePriorityLevel($level) ?? 5;

        return match ($level) {
            1 => 'EMG',
            2 => 'PW',
            3 => 'PR',
            4 => 'SN',
            default => 'GN',
        };
    }

    public static function generateUniqueQueueCode(int $priorityLevel): string
    {
        $prefix = self::prefixForLevel($priorityLevel);

        $tries = 0;
        do {
            $digits = str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
            $candidate = $prefix.'-'.$digits;
            $exists = DB::table('queues')->where('queue_code', $candidate)->exists();
            $tries++;
        } while ($exists && $tries < 25);

        return $candidate;
    }

    public static function basePriorityScore(?int $priorityLevel): int
    {
        $level = self::sanitizePriorityLevel($priorityLevel) ?? 5;
        $base = (6 - $level) * 1000;
        return max(0, (int) $base);
    }

    public static function waitScoreWeight(): int
    {
        $weight = (int) env('QUEUE_WAIT_SCORE_WEIGHT', 1);
        if ($weight < 0) {
            $weight = 0;
        }
        if ($weight > 60) {
            $weight = 60;
        }
        return $weight;
    }

    public function totalScore($now = null): int
    {
        $base = self::basePriorityScore($this->priority_level ?? 5);
        $weight = self::waitScoreWeight();

        $waited = 0;
        try {
            $ref = $now ?: now();
            $waited = $this->queue_datetime ? max(0, (int) $this->queue_datetime->diffInMinutes($ref)) : 0;
        } catch (\Throwable $e) {
            $waited = 0;
        }

        return $base + ($waited * $weight);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }
}
