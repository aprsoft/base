<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
	protected $guarded = ['id'];

    public function localities(){
		return $this->hasMany(Locality::class);		
	}

    public function province() {

        return $this->belongsTo(Province::class);
    }

    public function city() {

        return $this->belongsTo(City::class);
    }
}
