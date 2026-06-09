<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessedWaste extends Model
{
    protected $table = 'processed_waste';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'photo',
        'id_unit_measured',
        'default_measured_qty',
    ];

    public function unit()
    {
        return $this->belongsTo(UnitMeasured::class, 'id_unit_measured');
    }

    public function unitMeasured()
    {
        return $this->belongsTo(UnitMeasured::class, 'id_unit_measured');
    }

    public function processedWasteData()
    {
        return $this->hasMany(ProcessedWasteData::class, 'id_processed_waste');
    }
}
