<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->longText('Referencia')->nullable();
            $table->unsignedInteger('TpoDocRef')->nullable();
            $table->unsignedInteger('CodRef')->nullable();
            $table->longText('acuses')->nullable();
            $table->enum('fmaPago', ['CONTADO','CREDITO','SIN COSTO (ENTREGA GRATUITA)','NO INFORMADO'])->nullable();
            $table->integer('TpoTranCompra')->nullable();
            $table->integer('TpoTranVenta')->nullable();
            $table->integer('CdgSIISucur')->nullable();
            $table->unsignedBigInteger('expense_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('RUTSolicita', 191)->nullable();         
            $table->unsignedBigInteger('typedocument_id')->nullable();            
            $table->unsignedInteger('folio')->nullable();
            $table->enum('estado_documento', ['VIGENTE','NO VIGENTE'])->default('VIGENTE');
            $table->enum('condicion_documento', ['PAGADO','PENDIENTE','MOROSO','CONVENIO','ABONO|MOROSO','CONDONADO',
                'ANULADO','ELIMINADO','APLICADA','NO APLICADA','POR DEFINIR','RECHAZADO','CONTADO','CREDITO','SIN COSTO','INCOBRABLE'])->nullable();
            $table->integer('monto_exento')->nullable();
            $table->integer('monto_neto')->nullable();
            $table->integer('TasaIVA')->nullable();
            $table->integer('monto_iva')->nullable();
            $table->integer('monto_total')->nullable();
            $table->integer('valor_pagar')->nullable();
            $table->integer('monto_periodo')->nullable();             
            $table->date('fecha')->nullable();   
            $table->string('numero_orden', 191)->nullable();
            $table->enum('centro_costo', ['Operaciones','Administracion','Mantencion','Mejoramiento','Varios'])->nullable();
            $table->binary('timbre')->nullable();
            $table->date('fecha_resolucion')->nullable();
            $table->integer('numero_resolucion')->nullable();
            $table->string('nota', 191)->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('documents');            
            $table->foreign('typedocument_id')->references('id')->on('typedocuments');
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
