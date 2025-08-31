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
        Schema::create('change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // El usuario que solicita el cambio
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->json('data'); // Aquí guardaremos el 'sheet_data' con los cambios propuestos
            $table->text('comments')->nullable(); // Comentarios del solicitante
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Quién aprobó/rechazó finalmente
            $table->timestamp('decided_at')->nullable(); // Cuándo se tomó la decisión
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_requests');
    }
};
