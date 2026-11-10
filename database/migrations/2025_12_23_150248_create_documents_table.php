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
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            
            //---------------------------------------------------------------------

            $table->unsignedBigInteger('expense_id')->nullable();
            $table->unsignedInteger('customermeter_id')->nullable();
            $table->unsignedBigInteger('meter_id')->nullable();
            $table->unsignedBigInteger('reading_id')->nullable();
            $table->unsignedBigInteger('agreement_id')->nullable();
            $table->unsignedBigInteger('documentstate_id')->nullable();
            $table->unsignedBigInteger('documentstatu_id')->nullable();
            $table->unsignedBigInteger('typefolio_id')->nullable();
            $table->unsignedBigInteger('applicationstate_id')->nullable();
            $table->integer('monto_exento')->nullable();
            $table->integer('monto_neto')->nullable();
            $table->integer('TasaIVA')->nullable();
            $table->integer('monto_iva')->nullable();
            $table->integer('monto_total')->nullable();
            $table->integer('valor_pagar')->nullable();
            $table->integer('monto_periodo')->nullable();
            $table->integer('lecturaanterior')->nullable();
            $table->integer('lecturaactual')->nullable();
            $table->string('mes_cobro', 191)->nullable();
            $table->string('nota', 191)->nullable();
            $table->date('fecha_traspaso')->nullable();
            $table->unsignedBigInteger('previoustypedocument_id')->nullable();
            $table->date('fecha_pago')->nullable();
            $table->enum('centro_costo', ['Operaciones','Administracion','Mantencion','Mejoramiento','Varios'])->nullable();
            $table->unsignedBigInteger('caf_id')->nullable();

            //---------------------------------------------------------------------
            $table->longText('Referencia')->nullable();
            $table->unsignedInteger('TpoDocRef')->nullable();
            $table->unsignedInteger('CodRef')->nullable();
            $table->longText('acuses')->nullable();
            $table->enum('fmaPago', ['CONTADO','CREDITO','SIN COSTO (ENTREGA GRATUITA)','NO INFORMADO'])->nullable();
            $table->integer('TpoTranCompra')->nullable();
            $table->integer('TpoTranVenta')->nullable();
            $table->integer('CdgSIISucur')->nullable();          
            $table->string('RUTSolicita', 191)->nullable();         
            $table->unsignedBigInteger('typedocument_id')->nullable();            
            $table->unsignedInteger('folio')->nullable();
            $table->enum('estado_documento', ['VIGENTE','NO VIGENTE'])->default('VIGENTE');
            $table->enum('condicion_documento', ['PAGADO','PENDIENTE','MOROSO','CONVENIO','ABONO|MOROSO','CONDONADO',
                'ANULADO','ELIMINADO','APLICADA','NO APLICADA','POR DEFINIR','RECHAZADO','CONTADO','CREDITO','SIN COSTO','INCOBRABLE'])->nullable();                    
            $table->date('fecha')->nullable();   
            $table->string('numero_orden', 191)->nullable();
            $table->binary('timbre')->nullable();
            $table->date('fecha_resolucion')->nullable();
            $table->integer('numero_resolucion')->nullable();
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
