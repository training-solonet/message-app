<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'contact_name',
        'phone_number',
    ];

    public function schedules(){
        return $this->belongsToMany(Schedule::class, 'contact_schedule');
    }

    public function histories(){
        return $this->hasMany(History::class);
    }
}
