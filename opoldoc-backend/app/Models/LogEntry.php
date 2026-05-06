<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class LogEntry extends Model
{
    use HasFactory;

    protected $table = 'logs';

    protected $primaryKey = 'log_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'table_name',
        'record_id',
        'details',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public static function write(
        ?int $userId,
        string $action,
        string $tableName,
        ?int $recordId = null,
        array $details = [],
        ?int $dedupeSeconds = null
    ): void {
        $now = now();

        if ($dedupeSeconds && $dedupeSeconds > 0) {
            $since = $now->copy()->subSeconds($dedupeSeconds);

            $exists = static::query()
                ->where('action', $action)
                ->where('table_name', $tableName)
                ->when($recordId !== null, function ($q) use ($recordId) {
                    $q->where('record_id', $recordId);
                }, function ($q) {
                    $q->whereNull('record_id');
                })
                ->when($userId !== null, function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }, function ($q) {
                    $q->whereNull('user_id');
                })
                ->where('created_at', '>=', $since)
                ->exists();

            if ($exists) {
                return;
            }
        }

        static::create([
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'details' => $details ? json_encode($details) : null,
            'created_at' => $now,
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
