<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class History extends Model
{
    use HasFactory;
    
    protected $table = 'histories';
    protected $fillable = [ 'contact_id', 'message', 'direction', ];
    
    /** * Relasi ke Contact */
    public function contact() { 
        return $this->belongsTo(Contact::class); 
    }
}
