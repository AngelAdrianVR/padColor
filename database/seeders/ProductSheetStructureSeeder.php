<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductSheetTab;
use App\Models\ProductSheetField;
use App\Models\ProductSheetFieldOption;

class ProductSheetStructureSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar tablas para evitar duplicados en re-seeding
        ProductSheetFieldOption::query()->delete();
        ProductSheetField::query()->delete();
        ProductSheetTab::query()->delete();

        // Crear Pestañas
        $designTab = ProductSheetTab::create(['name' => 'Diseño', 'slug' => 'diseno', 'order' => 1, 'is_active' => true]);
        $finishesTab = ProductSheetTab::create(['name' => 'Acabados', 'slug' => 'acabados', 'order' => 2, 'is_active' => true]);
        $costsTab = ProductSheetTab::create(['name' => 'Costos y precios', 'slug' => 'costos_y_precios', 'order' => 3, 'is_active' => true]);

        // Poblar campos para cada pestaña
        $this->createDesignFields($designTab->id);
        $this->createFinishesFields($finishesTab->id);
        $this->createCostsAndPricesFields($costsTab->id);
    }

    private function createDesignFields(int $tabId): void
    {
        $createFieldWithOptions = function (array $fieldData, array $optionsData) use ($tabId) {
            $field = ProductSheetField::create(array_merge(['tab_id' => $tabId], $fieldData));
            foreach ($optionsData as $index => $option) {
                ProductSheetFieldOption::create([
                    'field_id' => $field->id,
                    'label' => $option['label'],
                    'value' => $option['value'],
                    'order' => $index + 1,
                ]);
            }
        };

        // --- SECCIÓN: Procesos de producción ---
        $createFieldWithOptions(
            ['section' => 'procesos_de_produccion', 'label' => 'Procesos', 'slug' => 'production_processes', 'type' => 'multicheckbox', 'order' => 10, 'is_active' => true],
            [
                ['label' => 'Hojeado', 'value' => 'hojeado'], ['label' => 'Guillotina', 'value' => 'guillotina'],
                ['label' => 'Offset', 'value' => 'offset'], ['label' => 'Barniz', 'value' => 'barniz'],
                ['label' => 'Laminado', 'value' => 'laminado'], ['label' => 'Pegado', 'value' => 'pegado'],
                ['label' => 'Estampado', 'value' => 'estampado'], ['label' => 'Realce', 'value' => 'realce'],
                ['label' => 'Suaje', 'value' => 'suaje'], ['label' => 'Máquila', 'value' => 'maquila'],
                ['label' => 'Otro', 'value' => 'otro']
            ]
        );

        // --- Las demás secciones de Diseño se mantienen igual ---
        ProductSheetField::create(['tab_id' => $tabId, 'section' => 'dimensiones_y_formatos_cm', 'label' => 'Tamaño de formato Tapas', 'slug' => 'format_size_lids', 'type' => 'text', 'order' => 20, 'is_active' => true]);
        ProductSheetField::create(['tab_id' => $tabId, 'section' => 'dimensiones_y_formatos_cm', 'label' => 'Bases', 'slug' => 'format_size_bases', 'type' => 'text', 'order' => 21, 'is_active' => true]);
        ProductSheetField::create(['tab_id' => $tabId, 'section' => 'dimensiones_y_formatos_cm', 'label' => 'Dimensiones del producto armado', 'slug' => 'product_dimensions', 'type' => 'text', 'order' => 22, 'is_active' => true]);
        
        $createFieldWithOptions(
            ['section' => 'tintas', 'label' => 'Tintas BASES', 'slug' => 'inks_bases', 'type' => 'multicheckbox', 'order' => 30, 'is_active' => true],
            [['label' => 'C', 'value' => 'C'], ['label' => 'M', 'value' => 'M'], ['label' => 'Y', 'value' => 'Y'], ['label' => 'K', 'value' => 'K']]
        );
        ProductSheetField::create(['tab_id' => $tabId, 'section' => 'tintas', 'label' => 'Tintas TAPAS', 'slug' => 'inks_lids', 'type' => 'text', 'order' => 31, 'is_active' => true]);
        $createFieldWithOptions(
            ['section' => 'tintas', 'label' => 'Tintas ETIQUETA', 'slug' => 'inks_label', 'type' => 'multicheckbox', 'order' => 32, 'is_active' => true],
            [['label' => 'C', 'value' => 'C'], ['label' => 'M', 'value' => 'M'], ['label' => 'Y', 'value' => 'Y'], ['label' => 'K', 'value' => 'K'], ['label' => 'Pantone ReflexBlue', 'value' => 'pantone_reflex_blue'], ['label' => 'Pantone', 'value' => 'pantone']]
        );
        
        $createFieldWithOptions(
            ['section' => 'impresion_y_terminados', 'label' => 'Impresión', 'slug' => 'impression_type', 'type' => 'radio', 'order' => 40, 'is_active' => true],
            [['label' => 'Frente', 'value' => 'frente'], ['label' => 'Vuelta', 'value' => 'vuelta'], ['label' => 'Frente y vuelta', 'value' => 'frente_vuelta']]
        );
        $createFieldWithOptions(
            ['section' => 'impresion_y_terminados', 'label' => 'Cara', 'slug' => 'impression_face_finish', 'type' => 'radio', 'order' => 41, 'is_active' => true],
            [['label' => 'Brillante', 'value' => 'brillante'], ['label' => 'Mate', 'value' => 'mate']]
        );
        $createFieldWithOptions(
            ['section' => 'impresion_y_terminados', 'label' => 'Vuelta de', 'slug' => 'impression_back_finish', 'type' => 'radio', 'order' => 42, 'is_active' => true],
            [['label' => 'Bandera', 'value' => 'bandera'], ['label' => 'Campana', 'value' => 'campana']]
        );
        $createFieldWithOptions(
            ['section' => 'impresion_y_terminados', 'label' => 'Terminados', 'slug' => 'finishes_list', 'type' => 'multicheckbox', 'order' => 43, 'is_active' => true],
            [
                ['label' => 'Barniz brillante', 'value' => 'barniz_brillante'], ['label' => 'Barniz mate', 'value' => 'barniz_mate'],
                ['label' => 'Laminado brillante', 'value' => 'laminado_brillante'], ['label' => 'Laminado mate', 'value' => 'laminado_mate'],
                ['label' => 'Realzado', 'value' => 'realzado'], ['label' => 'Estampado Oro / Plata', 'value' => 'estampado']
            ]
        );

        ProductSheetField::create(['tab_id' => $tabId, 'section' => 'informacion_de_suajes', 'label' => 'Suaje Tapa', 'slug' => 'die_cut_lid', 'type' => 'text', 'order' => 50, 'is_active' => true]);
        ProductSheetField::create(['tab_id' => $tabId, 'section' => 'informacion_de_suajes', 'label' => 'Suaje Base', 'slug' => 'die_cut_base', 'type' => 'text', 'order' => 51, 'is_active' => true]);
        $createFieldWithOptions(
            ['section' => 'informacion_de_suajes', 'label' => 'Tipo', 'slug' => 'die_cut_type', 'type' => 'radio', 'order' => 52, 'is_active' => true],
            [['label' => 'Nuevo', 'value' => 'nuevo'], ['label' => 'Existente', 'value' => 'existente']]
        );

        ProductSheetField::create(['tab_id' => $tabId, 'section' => 'accesorios', 'label' => 'Piezas por formato Tapas', 'slug' => 'pieces_per_format_lids', 'type' => 'text', 'order' => 60, 'is_active' => true]);
        ProductSheetField::create(['tab_id' => $tabId, 'section' => 'accesorios', 'label' => 'Base', 'slug' => 'pieces_per_format_base', 'type' => 'text', 'order' => 61, 'is_active' => true]);

        ProductSheetField::create(['tab_id' => $tabId, 'section' => 'observaciones_y_especificaciones', 'label' => 'Piezas por paquete', 'slug' => 'pieces_per_package', 'type' => 'text', 'order' => 71, 'is_active' => true]);
        $createFieldWithOptions(
            ['section' => 'observaciones_y_especificaciones', 'label' => 'Etiqueta', 'slug' => 'label_type', 'type' => 'radio', 'order' => 72, 'is_active' => true],
            [['label' => 'Incluida', 'value' => 'incluida'], ['label' => 'No lleva', 'value' => 'no_lleva'], ['label' => 'Aparte', 'value' => 'aparte']]
        );
        ProductSheetField::create(['tab_id' => $tabId, 'section' => 'observaciones_y_especificaciones', 'label' => 'Instrucciones adicionales', 'slug' => 'additional_instructions', 'type' => 'textarea', 'order' => 73, 'is_active' => true]);
    }

    private function createFinishesFields(int $tabId): void
    {
        $createFieldWithOptions = function (array $fieldData, array $optionsData) use ($tabId) {
            $field = ProductSheetField::create(array_merge(['tab_id' => $tabId], $fieldData));
            foreach ($optionsData as $index => $option) {
                ProductSheetFieldOption::create(['field_id' => $field->id, 'label' => $option['label'], 'value' => $option['value'], 'order' => $index + 1]);
            }
        };

        // --- SECCIÓN: Procesos de acabado ---
        ProductSheetField::create(['tab_id' => $tabId, 'section' => 'procesos_de_acabado', 'label' => 'Agujeteado', 'slug' => 'eyeleting', 'type' => 'text', 'order' => 1, 'is_active' => true]);
        ProductSheetField::create(['tab_id' => $tabId, 'section' => 'procesos_de_acabado', 'label' => 'Bolseado', 'slug' => 'bagging', 'type' => 'text', 'order' => 2, 'is_active' => true]);
        ProductSheetField::create(['tab_id' => $tabId, 'section' => 'procesos_de_acabado', 'label' => 'Termosellado', 'slug' => 'heat_sealing', 'type' => 'text', 'order' => 3, 'is_active' => true]);
        ProductSheetField::create(['tab_id' => $tabId, 'section' => 'procesos_de_acabado', 'label' => 'Caja', 'slug' => 'boxing', 'type' => 'text', 'order' => 4, 'is_active' => true]);
        $createFieldWithOptions(
            ['section' => 'procesos_de_acabado', 'label' => 'Adicionales', 'slug' => 'finish_extras', 'type' => 'multicheckbox', 'order' => 5, 'is_active' => true],
            [['label' => 'Hilaza', 'value' => 'hilaza'], ['label' => 'Bolsa', 'value' => 'bolsa']]
        );
        ProductSheetField::create(['tab_id' => $tabId, 'section' => 'procesos_de_acabado', 'label' => 'Notas', 'slug' => 'finish_notes', 'type' => 'textarea', 'order' => 6, 'is_active' => true]);
    }

    private function createCostsAndPricesFields(int $tabId): void
    {
        // --- SECCIÓN: Gestión financiera ---
        ProductSheetField::create(['tab_id' => $tabId, 'section' => 'gestion_financiera', 'label' => 'Costo total de producción (MXN)', 'slug' => 'total_production_cost', 'type' => 'text', 'order' => 1, 'is_active' => true]);
        ProductSheetField::create(['tab_id' => $tabId, 'section' => 'gestion_financiera', 'label' => 'Precio de venta (MXN / unidad)', 'slug' => 'unit_sale_price', 'type' => 'text', 'order' => 2, 'is_active' => true]);
    }
}