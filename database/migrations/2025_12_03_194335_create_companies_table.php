<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('rut_emisor',10);
            $table->string('razon_social');
            $table->string('giro_emisor');
            $table->string('direccion_origen');
            $table->string('acteco');
            $table->string('apikey');
            $table->string('url');
            $table->string('url_document_received');
            $table->string('url_document_detail');
            $table->string('codigo_sii_sucursal'); 
            $table->string('comuna_origen');       
            $table->string('contacto')->nullable();         
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
