<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['category_name'];

    public function contacts(){
        return $this->hasMany(Contact::class);
    }

    public function schedules(){
        return $this->belongsToMany(Schedule::class, 'contact_schedule', 'category_id', 'schedule_id');
    }
}
