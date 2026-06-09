<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessedWasteData extends Model
{
    protected $table = 'processed_waste_data';

    protected $fillable = [
        'id_processed_waste',
        'id_user',
        'measured_qty',
        'notes',
        'created_at',
    ];

    public function processedWaste()
    {
        return $this->belongsTo(ProcessedWaste::class, 'id_processed_waste');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
