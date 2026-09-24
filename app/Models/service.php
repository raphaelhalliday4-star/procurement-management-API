<?php

namespace App\Models;

use App\Models\Category;
use App\Models\PurchaseRequestItem;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'description',
        'type',
        'unit',
        'estimated_price',
        'status',
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function invoiceItem(){
        return $this->hasOne(InvoiceItem::class);
    }
}
