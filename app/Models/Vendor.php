<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
        protected $fillable = [
        'company_name',
        'registration_number',
        'email',
        'phone',
        'address',
        'tax_number',
        'bank_name',
        'bank_account',
        'status',
    ];

    public function documents(){
        return $this->hasMany(Vendor_document::class);
    }

    public function user(){
        return $this->hasMany(User::class);
    }

    public function invoice(){
        return $this->hasOne(Invoice::class);
    }
    
}
