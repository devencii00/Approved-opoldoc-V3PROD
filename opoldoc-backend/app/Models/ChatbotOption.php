<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotOption extends Model
{
    use HasFactory;

    protected $table = 'chatbot_system';

    protected $casts = [
        'is_starting_option' => 'boolean',
    ];

    protected $fillable = [
        'parent_id',
        'button_text',
        'response_text',
        'is_starting_option',
        'sort_order',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
