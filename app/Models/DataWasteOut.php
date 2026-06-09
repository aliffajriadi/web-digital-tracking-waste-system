<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataWasteOut extends Model
{
    protected $table = 'data_waste_out';

    public $timestamps = false;


    protected $fillable = [
        'id_waste_out_data',
        'is_processed_waste',
        'id_waste_sub_category',
        'id_processed_waste',
        'measured_qty',
    ];

    public function wasteOutData()
    {
        return $this->belongsTo(WasteOutData::class, 'id_waste_out_data');
    }

    public function wasteSubCategory()
    {
        return $this->belongsTo(WasteSubCategory::class, 'id_waste_sub_category');
    }

    public function processedWaste()
    {
        return $this->belongsTo(ProcessedWaste::class, 'id_processed_waste');
    }
}
