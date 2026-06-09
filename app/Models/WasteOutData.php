<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteOutData extends Model
{
    protected $table = 'waste_out_data';

    protected $fillable = [
        'id_user',
        'id_waste_out_method',
        'id_waste_destination',
        'notes',
        'created_at',
    ];

    public function attachment()
    {
        return $this->hasOne(AttachmentWasteOutData::class, 'id_waste_out_data');
    }

    public function dataWasteOut()
    {
        return $this->hasMany(DataWasteOut::class, 'id_waste_out_data');
    }

    public function sellingData()
    {
        return $this->hasOne(WasteSellingData::class, 'id_waste_out_data');
    }

    public function wasteOutMethod()
    {
        return $this->belongsTo(WasteOutMethod::class, 'id_waste_out_method');
    }

    public function wasteDestination()
    {
        return $this->belongsTo(WasteDestinations::class, 'id_waste_destination');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
