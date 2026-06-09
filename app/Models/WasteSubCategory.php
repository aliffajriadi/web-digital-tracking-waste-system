<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteSubCategory extends Model
{
    protected $table = 'waste_sub_category';
    public $timestamps = false;

    protected $fillable = [
        'id_waste_category',
        'name',
        'description',
        'id_waste_b3_detail',
        'photo',
        'is_active',
        'created_at',
        'id_unit_measured',
        'default_measured_qty',
    ];

    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return null;
    }

    public function category()
    {
        return $this->belongsTo(WasteCategory::class, 'id_waste_category');
    }

    public function b3Detail()
    {
        return $this->belongsTo(WasteB3Detail::class, 'id_waste_b3_detail');
    }

    public function unitMeasured()
    {
        return $this->belongsTo(UnitMeasured::class, 'id_unit_measured');
    }

    public function wasteEntries()
    {
        return $this->hasMany(WasteEntry::class, 'id_waste_sub_category');
    }
}
