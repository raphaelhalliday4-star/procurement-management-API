<?php

namespace App\Models;

use App\Models\Product\service;
use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id',
        'purchase_request_item_id',
        'service_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function purchaseRequestItem()
    {
        return $this->belongsTo(PurchaseRequestItem::class);
    }

    public function service()
    {
        return $this->belongsTo(service::class);
    }
}
