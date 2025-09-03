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
        Schema::create('product_sheet_tabs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nombre que se mostrará al usuario (ej. "Diseño")');
            $table->string('slug')->unique()->comment('Identificador para usar en el código (ej. "diseno")');
            $table->unsignedSmallInteger('order')->default(0)->comment('Para definir el orden de aparición');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_sheet_tabs');
    }
};
