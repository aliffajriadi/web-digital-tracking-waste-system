<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteDestinations extends Model
{
    protected $table = 'waste_destinations';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'location',
        'photo',
    ];

    public function wasteOutData()
    {
        return $this->hasMany(WasteOutData::class, 'id_waste_destination');
    }
}
