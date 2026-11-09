<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communes', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->foreignId('province_id')
                ->constrained('provinces')
                ->cascadeOnDelete();

            $table->foreignId('city_id')
                ->nullable()
                ->constrained('cities')
                ->nullOnDelete();
            
               
            // $table->unique(['name', 'province_id']);

            $table->timestamps();

           
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communes');
    }
};
