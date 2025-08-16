<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique()->comment('Clave única para usar en el código, ej: produccion.liberada');
            $table->string('name')->comment('Nombre descriptivo para el admin, ej: "Producción Liberada a Calidad"');
            $table->text('description')->nullable()->comment('Explicación de cuándo se dispara este evento');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_events');
    }
};
