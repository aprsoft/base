<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;


class Reading extends Model
{
    protected $guarded = ['id'];

    //------------------ Accesor/Mutator --------------------//


        protected function previousReading(): Attribute
        {
            return Attribute::make(
                get: fn () => $this->exists && $this->getKey()
                    ? $this->fetchPreviousReading()->lectura ?? 'sin registro'
                    : 'sin registro'
            );
        }

        protected function consumo(): Attribute
        {
            return Attribute::make(
                get: function() {

                    if ($this->lectura === 0){
                        return 'INICIO | MEDIDOR';

                    } elseif ($this->lectura < $this->fetchPreviousReading()->lectura) {   

                        return 'Error';
                    } else {

                        return $this->lectura - $this->fetchPreviousReading()->lectura;
                    }
                    
                }
            );
        }

    //------------------ Functions --------------------//

        public function fetchPreviousReading(): ?self
        {
            return self::where('meter_id', $this->meter_id)
                ->where('condicion_lectura', 'PROCESADA')
                ->where('estado_lectura', 'VIGENTE')
                ->where('validate', 'VALIDADA')
                ->where('id', '<', $this->id)
                ->orderByDesc('id')                
                ->first(); 
        }

     

    //------------------------- Functions ---------------------------------------//

        public static function initialMeterReading(int $meterId): self|false
        {
            
                $lastReadingForMeter = static::lastReadingForMeter($meterId);

                if ($lastReadingForMeter) {
                  
                    if ($lastReadingForMeter->lectura === 0) {
                        return false;
                    }

                    $lastReadingForMeter->update([
                        'condicion_lectura' => 'PROCESADA',
                        'validate'          => 'VALIDADA',
                    ]);
                }

                 return static::create([
                    'meter_id'          => $meterId,
                    'lectura'           => 0,
                    'mes_cobro'         => 'INICIO|MEDIDOR',
                    'estado_lectura'    => 'VIGENTE',
                    'condicion_lectura' => 'EN PROCESO',
                    'validate'          => 'VALIDADA',
                    'fecha'             => now()->toDateString(),
                ]);
               
        }

        public static function lastReadingForMeter(int $meterId): ?self
        {
            return static::where('meter_id', $meterId)
                ->where('estado_lectura', 'VIGENTE')
                ->where('condicion_lectura', 'EN PROCESO')
                ->where('validate', 'VALIDADA')
                ->latest('id')
                ->first();
        }


        /* Este metodo fuerza que siempre exista una lectura anterior, 
         es una proteccion en el caso que no se inicialicen todos los medidores con lectura en 0 en una nueva instalacion.*/
         
        public static function ensureCurrentForMeter(int $meterId): self
        {
            $current = static::lastReadingForMeter($meterId);

            if ($current === null) {
                static::initialMeterReading($meterId);
                $current = static::lastReadingForMeter($meterId);                
            }

            return $current;
        }

        public function scopeVigente($query)
		{
			return $query->where('estado_lectura', 'VIGENTE');
		}



  



}
