<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    protected $guarded = ['id'];

    public function locality() 
    {
        return $this->belongsTo(Locality::class);
    }
}
