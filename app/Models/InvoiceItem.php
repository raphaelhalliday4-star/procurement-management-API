<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 
        'purchase_order_item_id', 
        'service_id', 
        'quantity', 
        'unit_price', 
        'total_price',
    ];

    public function invoice(){
        return $this->belongsTo(Invoice::class);
    }

    public function item(){
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function service(){
        return $this->belongsTo(Service::class);
    }
}
