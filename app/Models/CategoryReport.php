<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryReport extends Model
{
    protected $table = 'category_report';

    public $timestamps = false;


    protected $fillable = [
        'name',
    ];

    public function reports()
    {
        return $this->hasMany(Report::class, 'id_category_report');
    }
}
