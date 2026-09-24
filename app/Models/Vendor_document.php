<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor_document extends Model
{
        protected $fillable = [
        'vendor_id',
        'document_type',
        'file_path',
        'status',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
