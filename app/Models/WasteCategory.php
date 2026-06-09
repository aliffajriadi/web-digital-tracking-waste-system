<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteCategory extends Model
{
    protected $table = 'waste_category';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'photo',
        'created_at',
    ];

    // Tambahan baru untuk photo
    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/categories/' . $this->photo);
        }
        return null;
    }

    public function subCategories()
    {
        return $this->hasMany(WasteSubCategory::class, 'id_waste_category');
    }
}
