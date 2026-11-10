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
            $table->unsignedInteger('IndExe')->nullable();
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('stretche_id')->nullable();
            //------------------------------------------------
            $table->unsignedBigInteger('detailstate_id')->nullable();
            $table->integer('cantidad')->nullable();
            $table->integer('valor')->nullable();
            $table->integer('neto')->nullable();
            $table->integer('iva')->nullable();
            $table->integer('total')->nullable();
            //-------------------------------------------------
            $table->unsignedInteger('DscItem')->nullable();
            $table->unsignedInteger('UnmdItem')->nullable();
            $table->unsignedInteger('DescuentoPct')->nullable();
            $table->unsignedInteger('DescuentoMonto')->nullable();
            $table->unsignedInteger('RecargoPct')->nullable();
            $table->unsignedInteger('RecargoMonto')->nullable();
            $table->unsignedInteger('QtyItem')->nullable();
            $table->unsignedInteger('PrcExeItem')->nullable();
            $table->unsignedInteger('PrcNetoItem')->nullable();
            $table->unsignedInteger('PrcItem')->nullable();
            $table->unsignedInteger('MontoExeItem')->nullable();  
            $table->unsignedInteger('MontoNetoItem')->nullable(); 
            $table->unsignedInteger('MontoItem')->nullable();  
            $table->integer('ivaItem')->nullable();
            $table->unsignedInteger('porcentaje_iva')->nullable();
            $table->string('detalle', 191)->nullable();
            $table->enum('estado_detalle', ['VIGENTE','NO VIGENTE'])->default('VIGENTE');
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
