<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'report';

    protected $fillable = [
        'id_user',
        'id_category_report',
        'title',
        'content',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function category()
    {
        return $this->belongsTo(CategoryReport::class, 'id_category_report');
    }

    public function categoryReport()
    {
        return $this->belongsTo(CategoryReport::class, 'id_category_report');
    }

    public function attachment()
    {
        return $this->hasOne(AttachmentReport::class, 'id_report');
    }
}
