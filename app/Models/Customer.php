<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Freshwork\ChileanBundle\Rut;


class Customer extends Model
{
	protected $guarded = ['id'];
	// protected $with = ['meter'];

//------------------ Relaciones --------------------//

	public function meter() {

		return $this->belongsToMany(Meter::class)
			->withPivot('id','estado_contrato')
			->wherePivot('estado_contrato','VIGENTE')
			->orderBy('id','desc')
			->withTimestamps();
    }

	public function meters() {
		return $this->belongsToMany(Meter::class)
			->withPivot('id','estado_contrato')
			->wherePivot('estado_contrato','VIGENTE')						
			->withTimestamps();
    }

	public function documents() {
        return $this->hasMany(Document::class);		
    }

//------------------ Accesor/Mutator --------------------//

	public function getFullNameAttribute() 
	{
		return $this->nombres.' '. $this->apellido_paterno.' '. $this->apellido_materno;
	}

	public function getRutFormatAttribute()
	{
        return Rut::parse($this->rut)->format();
    }

	public function getRutDteAttribute()
	{
        return Rut::parse($this->rut)->format(Rut::FORMAT_WITH_DASH);
    }	

	public function setRutAttribute($value)
	{
        $this->attributes['rut'] = Rut::parse($value)->format(Rut::FORMAT_WITH_DASH);
		// Rut::parse('12.345.678-5')->normalize(); //return '123456785'
    }


//-------------------------- functions --------------------------//

public function isCompany(): bool
	{
        return ($this->condicion_cliente === 'EMPRESA')? true:false;
    }
}