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
            'N° Orden',
            'Producto',
            'Temporada',
            'Cantidad solicitada',
            'Cantidad entregada',
            'Medida',
            'Cliente',
            'Progreso',
            'Notas',
            'Lista material',
            'Material',
            'Máquina',
            'Total hojas',
            'Fecha inicio',
            'Fecha esperada producción',
            'Fecha fin producción',
            'Fecha esperada empaque',
            'Fecha producto terminado',
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
            $production->start_date?->isoFormat('ddd, D MMM YYYY'),
            $production->estimated_date?->isoFormat('ddd, D MMM YYYY'),
            $production->close_production_date?->isoFormat('ddd, D MMM YYYY'),
            $production->estimated_package_date?->isoFormat('ddd, D MMM YYYY'),
            $production->finish_date?->isoFormat('ddd, D MMM YYYY'),
            $production->updated_at?->isoFormat('ddd, D MMM YYYY h:mm A'),
            $production->modifiedUser->name ?? ''
        ];
    }
}
