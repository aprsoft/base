<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Locality extends Model
{
	protected $guarded = ['id'];  
    
    public function sectors()
    {
		return $this->hasMany(Sector::class);		
	}    

    public function commune() 
    {
        return $this->belongsTo(Commune::class);
    }
}
