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
            ]
        ]);
    }
}

