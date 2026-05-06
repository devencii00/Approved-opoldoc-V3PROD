<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $table = 'medicines';

    protected $primaryKey = 'medicine_id';

    protected $fillable = [
        'generic_name',
        'brand_name',
        'indications',
        'contraindications',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function prescriptionItems()
    {
        return $this->hasMany(PrescriptionItem::class, 'medicine_id', 'medicine_id');
    }
}
