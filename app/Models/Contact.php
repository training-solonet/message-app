<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'contact_name',
        'category_id',
        'phone_number',
    ];

    public function histories(){
        return $this->hasMany(History::class);
    }

    public function category(){
        return $this->belongsTo(Category::class, 'category_id');
    }
}
