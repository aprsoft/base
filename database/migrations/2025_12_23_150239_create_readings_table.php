
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('readings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meter_id');
            $table->unsignedBigInteger('readingstate_id')->nullable();
            $table->unsignedBigInteger('readingstatu_id')->nullable();
            $table->enum('estado_lectura', ['VIGENTE','NO VIGENTE'])->default('VIGENTE');
            $table->enum('condicion_lectura', ['EN PROCESO','PROCESADA'])->default('EN PROCESO');
            $table->enum('validate', ['VALIDADA','NO VALIDADA'])->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('lectura');
            $table->string('mes_cobro', 191)->nullable();
            $table->tinyInteger('average')->nullable();
            $table->date('fecha')->nullable();
            $table->timestamps();

            $table->foreign('meter_id')->references('id')->on('meters');
           
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('readings');
    }
};

