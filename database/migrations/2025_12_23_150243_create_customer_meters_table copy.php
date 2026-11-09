<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_meter', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('meter_id');
            $table->unsignedBigInteger('customermeterstate_id')->nullable();
            $table->date('fecha_incorporacion')->nullable();
            $table->date('fecha_termino')->nullable();
            $table->enum('estado_contrato', ['VIGENTE','TERMINADO'])->default('VIGENTE');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('meter_id')->references('id')->on('meters');
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_meter');
    }
};
