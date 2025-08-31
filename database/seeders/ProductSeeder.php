<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creamos un usuario de ejemplo si no existe
        User::firstOrCreate(
            ['email' => 'gina@example.com'],
            [
                'name' => 'Gina',
                'password' => bcrypt('password')
            ]
        );

        // Creamos el producto "Caja Cubo de Fiesta"
        Product::create([
            'name' => 'Caja Cubo de Fiesta',
            'code' => 'N3847472F92',
            'description' => 'Microrrugado',
            'season' => 'Verano 2024',
            'material' => 'Caple 12 pt',
            'stock' => 100,
            'min_stock' => 20,
            'max_stock' => 200,
            'price' => 15.50,
            'sheet_data' => [
                // Datos para Identificación General
                'product_name' => 'Caja Cubo de Fiesta',
                'product_description' => 'Microrrugado',
                'season' => 'Verano 2024',
                'date' => '15/08/2024',
                'material' => 'Caple 12 pt',
                'sku' => '737346274384',
                'designer' => 'Gina',
                'client' => 'PadColor Insumos Gráficos',
                'revision' => '01',
                // ... (Datos existentes)
                'production_processes' => ['Offset', 'Barniz', 'Laminado', 'Pegado', 'Suaje'],
                'format_size_lids' => '64.5 x 42 cm',
                'format_size_bases' => '92 x 40.5 cm',
                'product_dimensions' => '22 x 22 + 22 cm',
                'inks_bases' => ['C', 'M', 'Y', 'K'],
                'inks_lids' => 'Verdes. Pantone 375 c',
                'inks_label' => 'Pantone ReflexBlue, C',
                'impression_type' => 'Frente',
                'impression_face' => 'Brillante',
                'impression_back' => '-',
                'finishes' => 'Barniz brillante - Fte',
                'die_cut_lid' => 'Caja cubo micro c22',
                'die_cut_base' => 'Caja cubo micro c22',
                'die_cut_base_2' => 'Nuevo',
                
                // Nuevos datos
                'pieces_per_format_lids' => '2',
                'pieces_per_format_base' => '1',
                'production_run' => '25 mil cajas',
                'label_info' => 'Incluida',
                'pieces_per_package' => '8 cajas surtidas de a 2 por modelo. (8 bases 8 tapas)',
                'additional_instructions' => "Laminar bases en single microrrugado flauta E (91.4x39.9)\nLaminar tapas en single microrrugado flauta E (91.4x39.9)",
                // Nuevos datos para Acabados
                'grommeting' => 'Rosa',
                'bagging' => 'Bolsa de celofán',
                'heat_sealing' => '-',
                'boxing' => '15',
                'additional_finishes' => ['Hilaza', 'Bolsa'],
                'finishes_notes' => 'Sin notas',

                // Nuevos datos para Costos y precios
                'total_production_cost' => '15,383.00',
                'sale_price_per_unit' => '25.5',

                'documents' => [
                    ['name' => 'Proof.pdf', 'size' => '25 KB', 'type' => 'pdf', 'url' => '#'],
                    ['name' => 'Packing list.jpg', 'size' => '35 KB', 'type' => 'img', 'url' => '#'],
                ]
            ]
        ]);
    }
}

