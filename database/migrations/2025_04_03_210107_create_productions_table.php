<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->string('folio');
            $table->string('client');
            $table->string('season')->nullable();
            $table->string('station');
            $table->string('dfi')->nullable();
            $table->unsignedSmallInteger('faces')->nullable();
            $table->unsignedFloat('changes', 8, 2)->nullable();
            $table->unsignedFloat('quantity', 11, 2);
            $table->unsignedFloat('current_quantity', 11, 2)->default(0);
            $table->unsignedFloat('close_date', 11, 2)->default(0);
            $table->unsignedFloat('width', 8, 2)->nullable();
            $table->unsignedFloat('large', 8, 2)->nullable();
            $table->unsignedFloat('gauge', 8, 2)->nullable();
            $table->unsignedFloat('pps', 8, 2)->nullable();
            $table->unsignedFloat('adjust', 8, 2)->nullable();
            $table->unsignedFloat('sheets', 8, 2)->nullable();
            $table->unsignedFloat('ha', 8, 2)->nullable();
            $table->unsignedFloat('pf', 8, 2)->nullable();
            $table->unsignedFloat('ts', 8, 2)->nullable();
            $table->unsignedFloat('ps', 8, 2)->nullable();
            $table->unsignedFloat('tps', 8, 2)->nullable();
            $table->string('look')->nullable();
            $table->json('materials')->nullable();
            $table->string('material')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained('machines')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('estimated_date')->nullable();
            $table->date('close_date')->nullable();
            $table->date('start_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
