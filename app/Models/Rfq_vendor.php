<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rfq_vendor extends Model
{
    protected $fillable = [
        'rfq_id',
        'vendor_id',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
