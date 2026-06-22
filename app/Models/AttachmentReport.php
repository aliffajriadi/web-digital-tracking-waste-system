<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttachmentReport extends Model
{
    protected $table = 'attachment_report';
    public $timestamps = false;

    protected $fillable = [
        'id_report',
        'path',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class, 'id_report');
    }
}
