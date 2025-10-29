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
        Schema::table('productions', function (Blueprint $table) {
            // Columna para la relación Padre-Hijo
            $table->foreignId('parent_production_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('productions')
                  ->onDelete('set null'); // Si el padre se borra, los hijos quedan huérfanos pero no se borran

            // Identificador de la parte (ej. 'A', 'B', 'C')
            $table->string('part_identifier')->nullable()->after('folio');

            // Cantidad de esta parte específica
            $table->decimal('part_quantity', 11, 2)->nullable()->after('quantity');

            // En la orden padre, esto rastreará cuánto falta por asignar
            $table->decimal('unassigned_quantity', 11, 2)->nullable()->after('part_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->dropForeign(['parent_production_id']);
            $table->dropColumn('parent_production_id');
            $table->dropColumn('part_identifier');
            $table->dropColumn('part_quantity');
            $table->dropColumn('unassigned_quantity');
        });
    }
};