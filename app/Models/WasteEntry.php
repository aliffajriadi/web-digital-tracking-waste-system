<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteEntry extends Model
{
    protected $table = 'waste_entry';

    protected $fillable = [
        'id_user',
        'id_waste_sub_category',
        'id_source_location_waste',
        'measured_qty',
        'notes',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function subCategory()
    {
        return $this->belongsTo(WasteSubCategory::class, 'id_waste_sub_category');
    }

    public function sourceLocation()
    {
        return $this->belongsTo(SourceLocationWaste::class, 'id_source_location_waste');
    }

    public function attachment()
    {
        return $this->hasOne(AttachmentWasteEntry::class, 'id_waste_entry');
    }
}
