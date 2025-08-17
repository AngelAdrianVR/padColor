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
        Schema::create('notification_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_event_id')->constrained('notification_events')->onDelete('cascade');

            // Usamos una relación polimórfica flexible.
            // 'notifiable_id' puede ser un ID de usuario (número) o un correo (string).
            // 'notifiable_type' será 'App\Models\User' o un identificador como 'external'.
            $table->string('notifiable_id');
            $table->string('notifiable_type');

            $table->timestamps();

            // Índice para búsquedas rápidas
            $table->index(['notifiable_id', 'notifiable_type']);

            // Clave única para evitar suscripciones duplicadas
            $table->unique(['notification_event_id', 'notifiable_id', 'notifiable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_subscriptions');
    }
};
