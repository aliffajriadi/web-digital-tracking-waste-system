<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteOutMethod extends Model
{
    protected $table = 'waste_out_method';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'photo',
    ];

    public function wasteOutData()
    {
        return $this->hasMany(WasteOutData::class, 'id_waste_out_method');
    }
}
