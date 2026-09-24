<?php

namespace App\Models;

use App\Models\Product\service;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    public function services(){
        return $this->hasMany(service::class);
    }
}
