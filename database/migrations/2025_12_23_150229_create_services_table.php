<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->unsignedBigInteger('taxe_id')->nullable();
            
            $table->enum('estado_servicio', ['VIGENTE','NO VIGENTE'])->default('VIGENTE');
            $table->enum('tipo_servicio', ['BASICOS','SERVICIOS','PROVEEDORES','SUBSIDIO','CONVENIO'])->default('SERVICIOS');
            $table->unsignedInteger('tramo_inicial')->nullable();
            $table->unsignedInteger('tramo_final')->nullable();
            $table->tinyInteger('es_tramo')->nullable();
            $table->integer('valor')->nullable();
            $table->integer('iva')->nullable();
            $table->integer('total')->nullable();
            $table->timestamps();

            $table->foreign('taxe_id')->references('id')->on('taxes');
           
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
