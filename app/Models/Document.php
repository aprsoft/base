<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Document extends Model
{
    protected $guarded = ['id'];

    public function getExentoAttribute(): int
    {
        if($this->customer_id){
        
            return round($this->details()->servicios()->whereNotNull('IndExe')->sum(DB::raw('PrcExeItem*QtyItem')));
        }
        if($this->supplier_id){
            return round($this->details()->sum('valor_exento'));
        }

        return 0;        
    }
   

    // Solo entrega la suma de los detalles de los servicios, el resto del los detalles los deja fuera(subtotales informativos, descuentos, subsidios,etc)
    public function getNetoAttribute(): int
    {
        if($this->customer_id){
            
            return round($this->details()->servicios()->whereNull('IndExe')->sum(DB::raw('PrcNetoItem*QtyItem')));
        }
         if($this->supplier_id){
            return round($this->details()->sum(DB::raw('PrcNetoItem*QtyItem')));
        }

        return 0;        
    }

    public function getIvaAttribute(): int
    {
        return round($this->details()->servicios()->whereNull('IndExe')->sum(DB::raw('MontoItem - (MontoItem / 1.19)')));
    }

    public function getTotalAttribute(): int
    {
   
        return round($this->getNetoAttribute() + $this->getIvaAttribute() +  $this->getExentoAttribute());
    }

    public function getRutAttribute(): ?string
    {
        return $this->customer?->rutFormat
            ?? $this->supplier?->rutFormat
            ?? $this->user?->rutFormat;
    }

    // Esta funcion entrega la descripcion dependiendo del tipo y condicion del documento
	public function getDetalleAttribute(): string
    {
        if ($this->customer) {
            
            if ($this->parent) {
                return sprintf(
                    'REF : %s - %s',
                    $this->parent->typedocument->abrev,
                    $this->parent->folio
                );
            }
            return 'Medidor N° '.$this->meter?->numero_medidor .' - '. ($this->reading?->mes_cobro ?? 'SERVICIO');
        }

        if ($this->supplier) {
            return 'COMPRA DE INSUMOS';
        }

        if ($this->employee) {
            return match ($this->typedocument->id) {
                7 => 'NOTA DE EGRESO',
                8 => 'NOTA DE INGRESO',
                default => 'POR DEFINIR',
            };
        }

        return 'POR DEFINIR';
    }

    public function getFullNameEntityAttribute(): string
    {
        return $this->customer?->fullName
            ?? $this->supplier?->fullName
            ?? $this->user?->fullName
            ?? 'POR DEFINIR';
    }

    public function parent()
    {
        return $this->belongsTo(Document::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Document::class, 'parent_id')
            ->where('estado_documento', 'VIGENTE');
    }

    public function details()
    {
        return $this->hasMany(Detail::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }  

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function meter()
    {
        return $this->belongsTo(Meter::class);
    }

    public function reading()
    {
        return $this->belongsTo(Reading::class);
    }

    public function typedocument() 
    {
        return $this->belongsTo(Typedocument::class);
    }

    // public function company() 
    // {
    //     return $this->belongsTo(Company::class);
    // }


    public function payment() {

        return $this->belongsToMany(Payment::class)
            ->withPivot('estado_abono','id','abono')
            ->wherePivot('estado_abono','VIGENTE')
            ->where('estado_pago','VIGENTE')
            ->withTimestamps();
    }

    //****************************** Scope ******************************//
   

		public function scopeVigente($query)
		{
			return $query->where('estado_documento', 'VIGENTE');
		}

        public function scopeNoVigente($query)
		{
			return $query->where('estado_documento', 'NO VIGENTE');
		}

        public function scopeSearchFullName($query, $term)
        {
            $query->whereRaw("CONCAT_WS(' ',nombres, apellido_paterno, apellido_materno) LIKE ?", ["%{$term}%"]);
        }

 

}
