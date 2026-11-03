<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedBigInteger('typecustomer_id')->default(1);
            $table->unsignedBigInteger('customercondition_id')->default(1);
            $table->string('rut',10)->nullable();
            $table->string('giro')->nullable();
            $table->string('nombres');
            $table->string('apellido_paterno', 191)->nullable();
            $table->string('apellido_materno', 191)->nullable();
            $table->string('direccion_cliente', 191)->nullable();
            $table->string('ciudad', 191)->nullable();
            $table->string('comuna', 191)->nullable();
            $table->string('telefono_fijo', 191)->nullable();
            $table->string('telefono_movil', 191)->nullable();
            $table->string('email_cliente', 191)->nullable();
            $table->enum('condicion_cliente', ['PARTICULAR','EMPRESA'])->default('PARTICULAR');
            $table->enum('tipo_cliente', ['SOCIO','CLIENTE','INTERNO'])->default('SOCIO');
            $table->unsignedInteger('region_id')->nullable();
            $table->unsignedInteger('province_id')->nullable();
            $table->unsignedInteger('commune_id')->nullable();
            $table->unsignedInteger('locality_id')->nullable();
            $table->unsignedInteger('sector_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamps();            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
