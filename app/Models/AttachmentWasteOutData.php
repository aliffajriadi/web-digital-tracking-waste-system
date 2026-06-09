<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttachmentWasteOutData extends Model
{
    protected $table = 'attachment_waste_out_data';
    protected $primaryKey = 'id_waste_out_data';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_waste_out_data',
        'path',
    ];

    public function wasteOutData()
    {
        return $this->belongsTo(WasteOutData::class, 'id_waste_out_data');
    }
}
