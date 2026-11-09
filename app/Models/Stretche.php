<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stretche extends Model
{
	protected $guarded = ['id'];


	//-------------------- Scope --------------------//

		public function scopeVigentes($query)
		{
			$query->where('estado_tramo', 'VIGENTE')
				->orderBy('inicio','asc');        
		}
    
}
