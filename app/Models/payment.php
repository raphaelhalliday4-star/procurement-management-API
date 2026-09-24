<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'payment_reference',
        'payment_date',
        'amount',
        'payment_method',
        'notes',
        'paid_by',
    ];

    public function invoice(){
        return $this->belongsTo(Invoice::class);
    }

    public function PaidBy(){
        
        return $this->belongs(User::class, 'paid_by');
    }
}
