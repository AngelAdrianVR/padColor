<?php

namespace Database\Seeders;

use App\Models\PortalPage;
use Illuminate\Database\Seeder;

class PortalPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['route_key' => 'pedidos', 'label' => 'Generador de Pedidos', 'url_path' => 'generador-pedidos', 'filename' => 'pedidos.blade.php'],
            ['route_key' => 'tutorial', 'label' => 'Tutorial de Pedidos', 'url_path' => 'tutorial-pedidos', 'filename' => 'tutorial.blade.php'],
            ['route_key' => 'catalogo', 'label' => 'Catálogo Toda Ocasión 2026', 'url_path' => 'catalogo-toda-ocasion-2026-san-felipe', 'filename' => 'catalogo.blade.php'],
            ['route_key' => 'buscador', 'label' => 'Buscador de Clientes', 'url_path' => 'buscador-clientes', 'filename' => 'buscador.blade.php'],
            ['route_key' => 'credito', 'label' => 'Solicitud de Crédito', 'url_path' => 'solicitud-credito', 'filename' => 'credito.blade.php'],
            ['route_key' => 'guias', 'label' => 'Buscador de Guías', 'url_path' => 'buscador-guias', 'filename' => 'guias.blade.php'],
            ['route_key' => 'prepedidos-muertoshalloween', 'label' => 'Prepedidos Halloween', 'url_path' => 'prepedidos-muertoshalloween', 'filename' => 'prepedidos-muertoshalloween.blade.php'],
            ['route_key' => 'buscador-metas', 'label' => 'Buscador de Metas', 'url_path' => 'buscador-metas', 'filename' => 'buscador-metas.blade.php'],
        ];

        foreach ($pages as $data) {
            PortalPage::firstOrCreate(
                ['route_key' => $data['route_key']],
                $data
            );
        }

        $this->command->info('Portal pages seeded: ' . PortalPage::count() . ' records.');
    }
}
