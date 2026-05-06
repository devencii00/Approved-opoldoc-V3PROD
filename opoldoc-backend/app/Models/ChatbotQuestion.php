<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotQuestion extends Model
{
    use HasFactory;

    protected $table = 'chatbot_questions';

    protected $primaryKey = 'question_id';

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'question_text',
        'is_active',
    ];

    public function getRouteKeyName()
    {
        return 'question_id';
    }

    public function options()
    {
        return $this->hasMany(ChatbotOption::class, 'question_id', 'question_id');
    }
}
