<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('details', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('nroLinDet')->nullable();
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->enum('estado_detalle', ['VIGENTE','NO VIGENTE'])->default('VIGENTE');
            $table->integer('cantidad')->nullable();
            $table->integer('valor')->nullable();
            $table->integer('neto')->nullable();
            $table->integer('iva')->nullable();
            $table->unsignedInteger('porcentaje_iva')->nullable();
            $table->integer('total')->nullable();
            $table->string('detalle', 191)->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('document_id')->references('id')->on('documents');
            $table->foreign('service_id')->references('id')->on('services');
          
           
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('details');
    }
};
