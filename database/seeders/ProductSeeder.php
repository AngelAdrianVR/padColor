<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Asegura que el usuario exista
        User::firstOrCreate(
            ['email' => 'gina@example.com'],
            ['name' => 'Gina', 'password' => bcrypt('password')]
        );

        // Crea o actualiza el producto de ejemplo
        Product::updateOrCreate(
            ['code' => 'N3847472F92'],
            [
                // --- Datos Maestros del Producto (antes en "Identificación General") ---
                'name' => 'Caja Cubo de Fiesta',
                'code' => '737346274384',
                'description' => 'Microrrugado',
                'season' => 'Verano 2024',
                'material' => 'Caple 12 pt',
                'created_at' => '2024-08-15 12:00:00', // Fecha de ejemplo

                // --- Ficha Técnica (sheet_data) ---
                'sheet_data' => [
                    // --- Pestaña: Diseño ---
                    'production_processes' => ['offset', 'barniz', 'laminado', 'pegado', 'suaje'],
                    'format_size_lids' => '64.5 x 42 cm',
                    'format_size_bases' => '92 x 40.5 cm',
                    'product_dimensions' => '22 x 22 + 22 cm',
                    'inks_bases' => ['C', 'M', 'Y', 'K'],
                    'inks_lids' => 'Verdes. Pantone 375 c',
                    'inks_label' => ['pantone_reflex_blue', 'C'],
                    'impression_type' => 'frente',
                    'impression_face_finish' => 'brillante',
                    'impression_back_finish' => null,
                    'finishes_list' => ['barniz_brillante'],
                    'die_cut_lid' => 'Caja cubo micro c22',
                    'die_cut_base' => 'Caja cubo micro c22',
                    'die_cut_type' => 'nuevo',
                    'pieces_per_format_lids' => '2',
                    'pieces_per_format_base' => '1',
                    'pieces_per_package' => '8 cajas surtidas de a 2 por modelo.',
                    'label_type' => 'incluida',
                    'additional_instructions' => "Laminar bases en single microrrugado flauta E (91.4x39.9)\nLaminar tapas en single microrrugado flauta E (91.4x39.9)",
                    
                    // --- Pestaña: Acabados ---
                    'eyeleting' => 'Rosa',
                    'bagging' => 'Bolsa de celofán',
                    'heat_sealing' => '-',
                    'boxing' => '15',
                    'finish_extras' => ['hilaza', 'bolsa'],
                    'finish_notes' => 'Sin notas',

                    // --- Pestaña: Costos y precios ---
                    'total_production_cost' => '10.5',
                    'unit_sale_price' => '25.5',
                ]
            ]
        );
    }
}