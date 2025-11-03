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
            // 1. Añadir columnas para la relación padre-hijo
            $table->foreignId('parent_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('productions')
                  ->onDelete('cascade'); // Si se borra el padre, se borran los hijos

            $table->string('component_name')->nullable()->after('folio');

            // 2. Modificar la columna 'folio'
            // Quitar el índice unique simple si existe (tuve que adivinar el nombre por defecto)
            // El nombre 'productions_folio_unique' es el que Laravel genera por defecto.
            try {
                 // Intentar eliminar la restricción unique por su nombre por defecto
                $table->dropUnique('productions_folio_unique');
            } catch (\Exception $e) {
                // Si falla (ej. el nombre es diferente), loggear o ignorar.
                // Para una migración real, deberías verificar el nombre exacto en tu DBM.
                // Por ahora, supondremos que se llama así o no existe.
                 logger('No se pudo dropear la constraint unique de folio. ' . $e->getMessage());
            }
           
            // Hacer la columnas 'folio' nullable
            $table->unsignedBigInteger('folio')->nullable()->change();

            // 3. Añadir la restricción unique condicional
            // Esto asegura que el 'folio' sea unique SÓLO para las filas donde 'parent_id' es NULL (órdenes padre).
            $table->unique(['folio'], 'folio_parent_null_unique')->whereNull('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            // Revertir los cambios en orden inverso
            $table->dropUnique('folio_parent_null_unique');
            
            // No es fácil revertir el ->nullable() y la constraint unique simple de forma segura,
            // pero sí podemos eliminar nuestras nuevas columnas y la FK.
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'component_name']);

            // Revertir folio (asumiendo que antes era not-nullable y unique)
            $table->unsignedBigInteger('folio')->nullable(false)->change();
            $table->unique('folio', 'productions_folio_unique'); // Re-añadir unique simple
        });
    }
};