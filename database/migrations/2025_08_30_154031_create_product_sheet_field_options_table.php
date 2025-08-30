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
        Schema::create('product_sheet_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained('product_sheet_fields')->onDelete('cascade');
            $table->string('label')->comment('La etiqueta que ve el usuario (ej. "Rojo")');
            $table->string('value')->comment('El valor que se guarda (ej. "rojo_01")');
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_sheet_field_options');
    }
};
