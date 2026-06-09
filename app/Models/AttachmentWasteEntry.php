<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttachmentWasteEntry extends Model
{
    protected $table = 'attachment_waste_entry';
    protected $primaryKey = 'id_waste_entry';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_waste_entry',
        'path',
    ];

    public function wasteEntry()
    {
        return $this->belongsTo(WasteEntry::class, 'id_waste_entry');
    }
}
