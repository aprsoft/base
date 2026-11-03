<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detail extends Model
{
   	protected $guarded=['id'];

	//****************************** Relaciones ******************************//

		public function document()
		{
			return $this->belongsTo(Document::class);
		}

		public function service()
		{
			return $this->belongsTo(Service::class);
		}

		public function stretche() 
		{
			return $this->belongsTo(Stretche::class);
		}

	//****************************** Scope ******************************//
   

		public function scopeVigente($query)
		{
			return $query->where('estado_detalle', 'VIGENTE');
		}

		public function scopeServicios($query)
		{
			return $query->whereNotIn('service_id',[9,11]);
		}

		public function scopeSubsidioConvenio($query)
		{
			return $query->whereIn('service_id',[9,11]);
		}

		public function scopeInformativo($query)
		{			
			return $query->whereIn('service_id', [9, 11]);
		}

		public function scopeDescuento($query)
		{			
			return $query->where('service_id', 10);
		}

		
}
