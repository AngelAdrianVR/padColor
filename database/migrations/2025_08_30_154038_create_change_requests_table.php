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
            $table->foreignId('requester_id')->constrained('users')->comment('Usuario que solicita el cambio');
            $table->foreignId('product_id')->constrained('products')->comment('Producto que se quiere modificar');
            $table->enum('status', ['pending', 'approved', 'rejected', 'processing'])->default('pending');
            $table->json('data_before')->comment('Estado del dato antes de los cambios');
            $table->json('data_after')->comment('Estado del dato con los cambios propuestos');
            $table->text('justification');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
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
