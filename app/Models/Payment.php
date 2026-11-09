<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
	protected $guarded=['id'];

   	public function documents()
	{
		return $this->belongsToMany(Document::class)
		->withPivot('estado_abono','id','abono')
		->wherePivot('estado_abono','VIGENTE')
		->withTimestamps();
	}

	public function documentPayments()
	{
		return $this->hasMany(DocumentPayment::class);
	}

	public function customer()
	{
		return $this->belongsTo(Customer::class);
	}

	public function getFullNameEntityAttribute(): string
    {
        return $this->customer?->fullName
            ?? $this->supplier?->fullName
            ?? $this->user?->fullName
            ?? 'POR DEFINIR';
    }



}
