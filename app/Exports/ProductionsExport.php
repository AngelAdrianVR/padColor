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
        foreach (range('A', 'T') as $columnID) {
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
            'Notas',
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
        return [
            $production->folio,
            $production->product->name ?? '',
            $production->product->season ?? '',
            $production->quantity,
            $production->close_quantity,
            $production->width . ' x ' . $production->large,
            $production->client,
            $production->station,
            $production->notes,
            $production->materials[0] ?? '',
            $production->material,
            $production->machine->name ?? '',
            $production->ts,
            $production->start_date?->isoFormat('DD/MM/YYYY'),
            $production->estimated_date?->isoFormat('DD/MM/YYYY'),
            $production->close_production_date?->isoFormat('DD/MM/YYYY'),
            $production->estimated_package_date?->isoFormat('DD/MM/YYYY'),
            $production->finish_date?->isoFormat('DD/MM/YYYY'),
            $production->updated_at?->isoFormat('DD/MM/YYYY h:mm A'),
            $production->modifiedUser->name ?? ''
        ];
    }
}
