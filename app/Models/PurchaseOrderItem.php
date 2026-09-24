<?php

namespace App\Models;

use App\Models\service;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'quotation_item_id',
        'service_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function quotationItem()
    {
        return $this->belongsTo(QuotationItem::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function invoiceItem(){
        return $this->hasMany(InvoiceItem::class);
    }
}
