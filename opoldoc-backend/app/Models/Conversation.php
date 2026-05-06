<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'conversations';

    protected $primaryKey = 'conversation_id';

    protected $fillable = [
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id', 'conversation_id');
    }

    public static function ensureForPatient(int $patientId): self
    {
        $conversation = self::query()
            ->where('user_id', $patientId)
            ->latest('conversation_id')
            ->first();

        if (! $conversation) {
            $conversation = self::create([
                'user_id' => $patientId,
            ]);

            return $conversation;
        }

        return $conversation;
    }
}
