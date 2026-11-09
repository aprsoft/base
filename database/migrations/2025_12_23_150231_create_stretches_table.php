<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stretches', function (Blueprint $table) {
            $table->id();      
            $table->string('name');           
            $table->unsignedInteger('inicio');
            $table->unsignedInteger('termino');        
            $table->unsignedInteger('neto');  
            $table->unsignedInteger('iva');     
            $table->unsignedInteger('total');  
            $table->enum('estado_tramo', ['VIGENTE', 'NO VIGENTE'])->default('VIGENTE');
            $table->timestamps();


            $table->foreignId('taxe_id')
                ->constrained('taxes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
          
            $table->index(['inicio', 'termino']);
            $table->index('estado_tramo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stretches');
    }
};
