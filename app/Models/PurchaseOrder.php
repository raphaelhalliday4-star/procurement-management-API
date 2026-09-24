<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'quotation_id',
        'vendor_id',
        'po_number',
        'delivery_address',
        'expected_delivery_date',
        'payment_terms',
        'tax',
        'discount',
        'total_amount',
        'status',
        'created_by',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function invoice(){
        return $this->hasOne(Invoice::class);
    }
}
