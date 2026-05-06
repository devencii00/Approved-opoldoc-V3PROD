<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GuestWalkInLink extends Model
{
    use HasFactory;

    protected $table = 'guest_walk_in_links';

    protected $primaryKey = 'link_id';

    protected $fillable = [
        'token',
        'created_by',
        'deprecated_at',
    ];

    protected $casts = [
        'deprecated_at' => 'datetime',
    ];

    public static function generateToken(): string
    {
        return Str::lower(Str::random(48));
    }

    public function isActive(): bool
    {
        return $this->deprecated_at === null;
    }
}

