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
        Schema::create('product_sheet_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tab_id')->constrained('product_sheet_tabs')->onDelete('cascade');
            $table->string('section')->nullable()->comment('Slug para agrupar campos en una tarjeta visual');
            $table->string('label')->comment('Etiqueta del campo (ej. "Material de las Tapas")');
            $table->string('slug')->unique()->comment('Identificador para usar como key en el JSON de datos');
            $table->string('type')->default('text');
            $table->json('options')->nullable()->comment('Opciones para campos select, multiselect, etc.');
            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_sheet_fields');
    }
};
