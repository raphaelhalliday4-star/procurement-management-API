<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'purchase_order_id', 
        'vendor_id', 
        'invoice_number', 
        'invoice_date', 
        'due_date', 
        'subtotal', 
        'tax', 
        'discount', 
        'total_amount', 
        'status', 
        'notes',
    ];

    public function purchaseOrder() {
         return $this->belongsTo(PurchaseOrder::class); 
    } 
         
    public function vendor() {
        return $this->belongsTo(Vendor::class); 
     }

    public function items() {
        return $this->hasMany(InvoiceItem::class); 
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function payment(){
        return $this->hasOne(payment::class);
    }
}
