<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'notifications';

    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'user_id',
        'type',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'bool',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public static function notifyAdmins(string $message, string $type = 'system'): void
    {
        static::notifyRole('admin', $message, $type);
    }

    public static function notifyReceptionists(string $message, string $type = 'system'): void
    {
        static::notifyRole('receptionist', $message, $type);
    }

    public static function notifyRole(string $role, string $message, string $type = 'system'): void
    {
        $userIds = User::query()
            ->where('role', $role)
            ->where('status', 'active')
            ->pluck('user_id')
            ->all();

        static::notifyUsers($userIds, $message, $type);
    }

    public static function notifyUsers(array $userIds, string $message, string $type = 'system'): void
    {
        $userIds = array_values(array_unique(array_filter(array_map(function ($id) {
            return is_numeric($id) ? (int) $id : null;
        }, $userIds))));

        if ($userIds === []) {
            return;
        }

        $now = now();

        static::query()->insert(array_map(function (int $userId) use ($message, $type, $now) {
            return [
                'user_id' => $userId,
                'type' => $type,
                'message' => $message,
                'is_read' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $userIds));
    }
}
