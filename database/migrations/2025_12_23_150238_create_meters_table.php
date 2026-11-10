

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('meters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();

            //------------------------------------------------------------------------

            $table->unsignedBigInteger('meterstate_id')->nullable();
            $table->unsignedBigInteger('metertype_id')->nullable();
            $table->unsignedBigInteger('sector_id3')->nullable();

            //------------------------------------------------------------------------
            
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('numero_medidor')->unique();
            $table->string('direccion_medidor', 191)->nullable();
            $table->string('ciudad', 191)->nullable();
            $table->string('comuna', 191)->nullable();
            $table->string('sector', 191)->nullable();
            $table->string('email_medidor', 191)->nullable();
            $table->string('responsable', 191)->nullable();
            $table->string('rol', 191)->nullable();
            $table->enum('tipo_medidor', ['MICROMEDIDOR','REMARCADOR','MACROMEDIDOR'])->default('MICROMEDIDOR');
            $table->enum('estado_medidor', ['ACTIVO','RETIRADO','SUSPENDIDO','RESERVADO','TERMINADO'])->default('ACTIVO');
            $table->unsignedBigInteger('region_id')->nullable();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('commune_id')->nullable();
            $table->unsignedBigInteger('locality_id')->nullable();
            $table->unsignedBigInteger('sector_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->timestamps();

            $table->foreign('sector_id')->references('id')->on('sectors');

           
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meters');
    }
};

