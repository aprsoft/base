<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('typedocuments', function (Blueprint $table) {
            $table->id(); // bigint unsigned auto_increment

            $table->string('name', 191);

            $table->enum('transacciontype', [
                'INGRESO',
                'EGRESO',
                'AJUSTE',
                'CORRECCION',
                'NO APLICA'
            ])->nullable();

            $table->enum('tipo', [
                'TRIBUTARIO',
                'CONTROL INTERNO'
            ])->nullable();

            $table->unsignedInteger('codigo')->nullable();

            $table->string('abrev', 191)->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('typedocuments');
    }
};
