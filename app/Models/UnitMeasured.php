<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitMeasured extends Model
{
    protected $table = 'unit_measured';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'type',
        'symbol',
    ];

    public function wasteSubCategories()
    {
        return $this->hasMany(WasteSubCategory::class, 'id_unit_measured');
    }
}
