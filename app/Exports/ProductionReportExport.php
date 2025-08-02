<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductionReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $productions;

    public function __construct($productions)
    {
        $this->productions = $productions;
    }

    public function collection()
    {
        return $this->productions;
    }

    public function styles(Worksheet $sheet)
    {
        // Negritas en los encabezados
        $sheet->getStyle(1)->getFont()->setBold(true);

        // Autoajustar el ancho de las columnas
        foreach (range('A', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        return [];
    }

    public function headings(): array
    {
        return [
            'Producto',
            'Temporada',
            'N° Orden',
            'Tipo de entrega',
            'Parcial 1°',
            'Parcial N°',
            'Cantidad final',
            'Restante',
        ];
    }

    public function map($production): array
    {
        // Obtener cantidad de parcial 1
        $partial1 = $production->partials ? $production->partials[0]['quantity'] : 0;
        // Obtener cantidad de parcial N que es la suma de todos los parciales menos el primero
        $partialN = $production->partials ? array_sum(array_column($production->partials, 'quantity')) - $partial1 : 0;
        return [
            $production->product->name ?? '',
            $production->product->season ?? '',
            $production->folio,
            $production->production_close_type ?? '',
            $partial1,
            $partialN,
            $production->current_quantity,
            ($production->quantity - $production->current_quantity) < 0 ? '0' : ($production->quantity - $production->current_quantity),
        ];
    }
}
