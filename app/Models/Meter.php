<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Meter extends Model
{
    protected $guarded = ['id'];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function customer()
    {
        return $this->belongsToMany(Customer::class)
            ->withPivot('id', 'estado_contrato')
            ->wherePivot('estado_contrato', 'VIGENTE')
            ->orderBy('id', 'desc')
            ->withTimestamps();
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class)
            ->withPivot('id', 'estado_contrato')            
            ->orderBy('id', 'desc')
            ->withTimestamps();
    }

    public function contract()
    {
        return $this->hasMany(CustomerMeter::class)
            ->where('estado_contrato','VIGENTE');        
    }

    public function contracts()
    {
        return $this->hasMany(CustomerMeter::class)                    
            ->orderBy('id', 'desc')
            ->withTimestamps();
    }

    public function readings()
    {
        return $this->hasMany(Reading::class);
    }

    public function parent()
    {
        return $this->belongsTo(Meter::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Meter::class, 'parent_id');            
    }

    public function customerMeters()
    {
        return $this->hasMany(CustomerMeter::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);           
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): void
    {
        $query->orWhere('estado_medidor', 'ACTIVO');
    }

    public function scopeSuspended(Builder $query): void
    {
        $query->orWhere('estado_medidor', 'SUSPENDIDO');
    }

     /*
    |--------------------------------------------------------------------------
    | Functions
    |--------------------------------------------------------------------------
    */

        public static function nextNewMeterNumber(): int
        {
            return (int) (
                self::whereIn('estado_medidor', ['ACTIVO', 'RESERVADO'])
                    ->max('numero_medidor') ?? 0
            ) + 1;
        }


    
    //-------------------- Scope   --------------------//

        public function scopeContractStatus($query,$estado){
            return $query->where('estado_medidor',$estado)->get();
        }

        public function scopeActivos($query){
            return $query->where('estado_medidor','ACTIVO')->get();
        }

        public function scopeReservados($query){
             $query->where('estado_medidor','RESERVADO')->get();
        }

        public function scopeActivosReservados($query){
            return $query->whereIn('estado_medidor',[1,4])->orderBy('numero_medidor','asc')->get();
        }

        public function promedioConsumo(): float
        {
            $readings = $this->readings()
                ->orderBy('created_at')
                ->pluck('lectura');

            if ($readings->count() < 2) {
                return 0;
            }

            $previous = null;
            $total = 0;
            $count = 0;

            foreach ($readings as $reading) {

                if ($previous !== null) {
                    $consumo = $reading < $previous
                        ? $reading
                        : $reading - $previous;

                    $total += $consumo;
                    $count++;
                }

                $previous = $reading;
            }

            return $count > 0
                ? round($total / $count, 2)
                : 0;
        }

    
}
