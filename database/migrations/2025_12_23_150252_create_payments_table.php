<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('typedocumentpayment_id')->nullable();
            $table->enum('tipo_movimiento', ['INGRESO','EGRESO'])->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('expense_id')->nullable();
            $table->unsignedBigInteger('paymentstate_id')->nullable();
            $table->unsignedBigInteger('waytopay_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->enum('estado_pago', ['VIGENTE','NO VIGENTE'])->default('VIGENTE');
            $table->enum('forma_pago', [
                'EFECTIVO','TRANSFERENCIA BANCARIA','CREDITO','CHEQUE',
                'DEPOSITO BANCARIO','TARJETA DEBITO','TARJETA CREDITO','CONDONACION','SIN INFORMACION','INCOBRABLE'
            ])->nullable();
            $table->unsignedInteger('folio')->nullable();
            $table->unsignedBigInteger('numero_comprobante')->nullable();
            $table->string('nombre_completo', 191)->nullable();
            $table->text('detalle')->nullable();
            $table->unsignedInteger('descuento')->nullable();
            $table->unsignedInteger('exento')->nullable();
            $table->unsignedInteger('iva')->nullable();
            $table->unsignedInteger('neto')->nullable();
            $table->integer('monto');
            $table->date('fecha')->nullable();
            $table->timestamps();

          
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
