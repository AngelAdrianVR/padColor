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
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique();
            
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('customs_agent_id')->constrained('customs_agents');
            $table->foreignId('user_id')->comment('Usuario creador')->constrained('users');

            $table->string('incoterm')->nullable();
            $table->string('status')->default('proveedor')->index();

            $table->date('estimated_ship_date')->nullable();
            $table->date('estimated_arrival_date')->nullable();
            $table->date('actual_arrival_date')->nullable();
            $table->date('warehouse_delivery_date')->nullable();
            
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imports');
    }
};
