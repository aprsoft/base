<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_proveedor', ['PARTICULAR','EMPRESA'])->default('EMPRESA');
            $table->longText('Acteco')->nullable();
            $table->string('rut', 191);
            $table->string('nombres', 191);
            $table->string('apellido_paterno', 191)->nullable();
            $table->string('apellido_materno', 191)->nullable();
            $table->string('giro_proveedor', 191);
            $table->string('direccion_proveedor', 191);
            $table->string('comuna', 191)->nullable();
            $table->string('ciudad', 191)->nullable();
            $table->string('telefono_fijo', 191)->nullable();
            $table->string('telefono_movil', 191)->nullable();
            $table->string('email_proveedor', 191)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
