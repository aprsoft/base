<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CustomerMeter extends Pivot
{
    protected $guarded = ['id'];

    public function customer()
	{
		return $this->belongsTo(Customer::class);
	}

	public function meter()
	{
		return $this->belongsTo(Meter::class);
	}

//------------------- Scope ------------------- //

    public function scopeVigentes($query)
    {      
        return $query->where('estado_contrato','VIGENTES');      
    }

    public function scopeTerminados($query)
    {      
        return $query->where('estado_contrato','TERMINADO');      
    }
    
}
