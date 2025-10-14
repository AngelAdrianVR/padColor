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
            $table->unsignedBigInteger('folio');
            $table->string('client');
            $table->string('season')->nullable();
            $table->string('station');
            $table->string('dfi')->nullable();
            $table->string('gauge')->nullable();
            $table->string('production_close_type')->nullable();
            $table->unsignedSmallInteger('faces')->nullable();
            $table->unsignedFloat('changes', 8, 2)->nullable();
            $table->unsignedFloat('quantity', 11, 2);
            $table->unsignedFloat('current_quantity', 11, 2)->default(0);
            $table->unsignedFloat('scrap_quantity', 11, 2)->default(0);
            $table->unsignedFloat('shortage_quantity', 11, 2)->default(0);
            $table->unsignedFloat('production_scrap', 11, 2)->default(0);
            $table->unsignedFloat('production_shortage', 11, 2)->default(0);
            $table->unsignedFloat('quality_scrap', 11, 2)->default(0);
            $table->unsignedFloat('quality_shortage', 11, 2)->default(0);
            $table->unsignedFloat('inspection_scrap', 11, 2)->default(0);
            $table->unsignedFloat('inspection_shortage', 11, 2)->default(0);
            $table->unsignedFloat('close_quantity', 11, 2)->default(0);
            $table->unsignedFloat('quality_quantity', 11, 2)->default(0);
            $table->text('close_production_notes')->nullable();
            $table->text('quality_notes')->nullable();
            $table->json('returns')->nullable();
            $table->unsignedFloat('width', 8, 2)->nullable();
            $table->unsignedFloat('large', 8, 2)->nullable();
            $table->unsignedFloat('pps', 8, 2)->nullable();
            $table->unsignedFloat('adjust', 8, 2)->nullable();
            $table->unsignedFloat('sheets', 8, 2)->nullable();
            $table->unsignedFloat('ha', 8, 2)->nullable();
            $table->unsignedFloat('pf', 8, 2)->nullable();
            $table->unsignedFloat('ts', 8, 2)->nullable();
            $table->unsignedFloat('ps', 8, 2)->nullable();
            $table->unsignedFloat('tps', 8, 2)->nullable();
            $table->string('varnish_type')->nullable();
            $table->json('partials')->nullable();
            $table->string('look')->nullable();
            $table->json('materials')->nullable();
            $table->string('material')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained('machines')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('modified_user_id')->constrained('users')->cascadeOnDelete();
            $table->date('estimated_date')->nullable();
            $table->date('estimated_package_date')->nullable();
            $table->timestamp('close_production_date')->nullable();
            $table->timestamp('quality_released_date')->nullable();
            $table->date('start_date')->nullable();
            $table->timestamp('finish_date')->nullable();

            $table->decimal('packing_received_quantity', 11, 2)->nullable();
            $table->dateTime('packing_received_date')->nullable();
            $table->string('packing_close_type')->nullable();
            $table->text('packing_notes')->nullable();
            $table->decimal('packing_scrap', 11, 2)->default(0);
            $table->decimal('packing_shortage', 11, 2)->default(0);
            $table->json('packing_partials')->nullable();
            $table->dateTime('packing_finished_date')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
