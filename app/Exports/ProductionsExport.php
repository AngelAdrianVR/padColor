<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
        foreach (range('A', 'X') as $columnID) {
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
            'Cliente',
            'Progreso',
            'Descripción',
            'Lista material',
            'Máquina',
            'Cantidad programada',
            'Material',
            'Medida',
            'Total hojas',
            'Fecha inicio',
            'Fecha esperada producción',
            'Fecha fin producción',
            'Cantidad entregada',
            'Fecha esperada empaque',
            'Producto terminado',
            'Parcial 1°',
            'Parcial N°',
            'Cantidad final',
            'Última modificación',
            'Modificado por'
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
            $production->client,
            $production->station,
            $production->notes,
            $production->materials[0] ?? '',
            $production->machine->name ?? '',
            $production->quantity ?? 0,
            $production->material,
            $production->width . ' x ' . $production->large,
            $production->ts,
            $production->start_date?->isoFormat('YYYY/DD/MM'),
            $production->estimated_date?->isoFormat('DD/MM/YYYY'),
            $production->close_production_date?->toDateTimeString(),
            $production->close_quantity,
            $production->estimated_package_date?->isoFormat('DD/MM/YYYY'),
            $production->finish_date?->toDateTimeString(),
            $partial1,
            $partialN,
            $production->current_quantity,
            $production->updated_at?->toDateTimeString(),
            $production->modifiedUser->name ?? ''
        ];
    }
}
