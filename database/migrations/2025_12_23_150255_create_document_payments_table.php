<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('document_payment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('documentpaymentstate_id')->nullable();
            $table->enum('estado_abono', ['VIGENTE','NO VIGENTE'])->default('VIGENTE');
            $table->integer('abono')->nullable();
            $table->timestamps();

            $table->foreign('document_id')->references('id')->on('documents');
            $table->foreign('payment_id')->references('id')->on('payments');
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_payment');
    }
};
