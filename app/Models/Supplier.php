<?php

namespace App\Models;

use Freshwork\ChileanBundle\Rut;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $guarded = ['id'];

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

	public function isCompany(): bool
	{
        return ($this->condicion_proveedor === 'EMPRESA')? true:false;
    }
}
