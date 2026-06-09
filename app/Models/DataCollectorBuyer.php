<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataCollectorBuyer extends Model
{
    protected $table = 'data_collector_buyer';


    protected $fillable = [
        'name',
        'phone_number',
        'address',
        'email',
        'website',
        'notes',
        'created_at',
    ];

    public function wasteSellingData()
    {
        return $this->hasMany(WasteSellingData::class, 'id_buyer');
    }
}
