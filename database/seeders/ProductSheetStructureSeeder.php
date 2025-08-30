<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductSheetTab;
use App\Models\ProductSheetField;

class ProductSheetStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear la pestaña de "Diseño"
        $designTab = ProductSheetTab::create([
            'name' => 'Diseño',
            'slug' => 'diseno',
            'order' => 1,
            'is_active' => true,
        ]);

        // 2. Crear los campos para la pestaña de "Diseño", agrupados por sección

        // Sección: Procesos de producción
        ProductSheetField::create([
            'tab_id' => $designTab->id, 'section' => 'procesos_de_produccion', 'label' => 'Procesos',
            'slug' => 'production_processes', 'type' => 'multiselect', 'order' => 10, 'is_active' => true,
            'options' => json_encode(['Offset', 'Barniz', 'Laminado', 'Pegado', 'Suaje']),
        ]);

        // Sección: Dimensiones y formatos (cm)
        ProductSheetField::create([
            'tab_id' => $designTab->id, 'section' => 'dimensiones_y_formatos_cm', 'label' => 'Tamaño de formato Tapas',
            'slug' => 'format_size_lids', 'type' => 'text', 'order' => 20, 'is_active' => true
        ]);
        ProductSheetField::create([
            'tab_id' => $designTab->id, 'section' => 'dimensiones_y_formatos_cm', 'label' => 'Bases',
            'slug' => 'format_size_bases', 'type' => 'text', 'order' => 21, 'is_active' => true
        ]);
        ProductSheetField::create([
            'tab_id' => $designTab->id, 'section' => 'dimensiones_y_formatos_cm', 'label' => 'Medida Producto armado',
            'slug' => 'product_dimensions', 'type' => 'text', 'order' => 22, 'is_active' => true
        ]);
        
        // Sección: Tintas
        ProductSheetField::create([
            'tab_id' => $designTab->id, 'section' => 'tintas', 'label' => 'Tintas BASES',
            'slug' => 'inks_bases', 'type' => 'checklist', 'order' => 30, 'is_active' => true,
            'options' => json_encode(['C', 'M', 'Y', 'K']),
        ]);
        ProductSheetField::create([
            'tab_id' => $designTab->id, 'section' => 'tintas', 'label' => 'Tintas TAPAS',
            'slug' => 'inks_lids', 'type' => 'text', 'order' => 31, 'is_active' => true
        ]);
        ProductSheetField::create([
            'tab_id' => $designTab->id, 'section' => 'tintas', 'label' => 'Tintas ETIQUETA',
            'slug' => 'inks_label', 'type' => 'text', 'order' => 32, 'is_active' => true
        ]);

        // Sección: Impresión y terminados
        ProductSheetField::create([ 'tab_id' => $designTab->id, 'section' => 'impresion_y_terminados', 'label' => 'Impresión', 'slug' => 'impression_type', 'type' => 'text', 'order' => 40, 'is_active' => true ]);
        ProductSheetField::create([ 'tab_id' => $designTab->id, 'section' => 'impresion_y_terminados', 'label' => 'Cara', 'slug' => 'impression_face', 'type' => 'text', 'order' => 41, 'is_active' => true ]);
        ProductSheetField::create([ 'tab_id' => $designTab->id, 'section' => 'impresion_y_terminados', 'label' => 'Vuelta de', 'slug' => 'impression_back', 'type' => 'text', 'order' => 42, 'is_active' => true ]);
        ProductSheetField::create([ 'tab_id' => $designTab->id, 'section' => 'impresion_y_terminados', 'label' => 'Terminados', 'slug' => 'finishes', 'type' => 'text', 'order' => 43, 'is_active' => true ]);

        // Sección: Información de suajes
        ProductSheetField::create([ 'tab_id' => $designTab->id, 'section' => 'informacion_de_suajes', 'label' => 'Suaje Tapa', 'slug' => 'die_cut_lid', 'type' => 'text', 'order' => 50, 'is_active' => true ]);
        ProductSheetField::create([ 'tab_id' => $designTab->id, 'section' => 'informacion_de_suajes', 'label' => 'Suaje Base', 'slug' => 'die_cut_base', 'type' => 'text', 'order' => 51, 'is_active' => true ]);
        ProductSheetField::create([ 'tab_id' => $designTab->id, 'section' => 'informacion_de_suajes', 'label' => 'Suaje Base', 'slug' => 'die_cut_base_2', 'type' => 'text', 'order' => 52, 'is_active' => true ]);

    }
}

