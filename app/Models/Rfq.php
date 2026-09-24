<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rfq extends Model
{
    protected $casts = [
        'opening_date' => 'datetime',
        'closing_date' => 'datetime',
    ];

    protected $fillable = [
        'purchase_request_id',
        'rfq_number',
        'title',
        'description',
        'opening_date',
        'closing_date',
        'created_by',
        'status',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function quotation(){
        return $this->hasMany(Quotation::class);
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'rfq_vendors', 'rfq_id', 'vendor_id');
    }
}
