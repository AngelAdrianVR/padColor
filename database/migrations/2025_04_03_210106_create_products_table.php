<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('season')->nullable();
            $table->string('branch')->nullable(); //sucursal
            $table->string('measure_unit')->nullable(); //Unidad de medida
            $table->unsignedSmallInteger('width')->nullable(); //Ancho del producto
            $table->unsignedSmallInteger('large')->nullable(); //Largo o medida del producto
            $table->unsignedSmallInteger('height')->nullable(); //Alto o medida del producto
            $table->string('material')->nullable();
            $table->unsignedFloat('stock', 11, 2)->nullable();
            $table->unsignedFloat('min_stock', 11, 2)->nullable();
            $table->unsignedFloat('max_stock', 11, 2)->nullable();
            $table->unsignedFloat('price', 11, 2)->nullable();
            $table->json('sheet_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
