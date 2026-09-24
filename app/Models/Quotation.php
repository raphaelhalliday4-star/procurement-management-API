<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'rfq_id',
        'vendor_id',
        'quotation_number',
        'quotation_date',
        'valid_until',
        'status',
        'total_amount',
        'notes',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

}
