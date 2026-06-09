<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteRawMaterials extends Model
{
    protected $table = 'waste_raw_materials';

    public $timestamps = false;

    protected $fillable = [
        'id_processed_waste_data',
        'id_waste_sub_category',
        'measured_qty',
    ];

    public function processedWasteData()
    {
        return $this->belongsTo(ProcessedWasteData::class, 'id_processed_waste_data');
    }

    public function wasteSubCategory()
    {
        return $this->belongsTo(WasteSubCategory::class, 'id_waste_sub_category');
    }
}
