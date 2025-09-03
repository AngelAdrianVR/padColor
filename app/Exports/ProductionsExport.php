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

    /**
     * Calcula el porcentaje de un valor sobre un total, manejando divisiones por cero.
     *
     * @param float|int $value
     * @param float|int $total
     * @return float
     */
    private function getPercentage($value, $total)
    {
        if (empty($total) || empty($value)) {
            return 0;
        }
        return ($value / $total) * 100;
    }

    public function styles(Worksheet $sheet)
    {
        // Negritas en los encabezados
        $sheet->getStyle(1)->getFont()->setBold(true);

        // Autoajustar el ancho de las columnas (rango extendido para nuevas columnas)
        foreach (range('A', 'AU') as $columnID) {
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
            'Cantidad entregada (Producción)',
            // 'Merma (Producción)',
            // 'Merma % (Producción)',
            // 'Diferencia (Producción)',
            'Cantidad entregada (Calidad)',
            'Merma (Calidad)',
            'Merma % (Calidad)',
            'Diferencia (Calidad)',
            'Cantidad final (Inspección)',
            'Merma (Inspección)',
            'Merma % (Inspección)',
            'Diferencia (Inspección)',
            'Merma (Total)',
            'Diferencia (Total)',
            'Restante',
            'Fecha esperada empaque',
            'Producto terminado',
            'Parcial 1°',
            'Parcial N°',
            'Dimensión del formato de impresión',
            'Piezas por hoja',
            'Ajuste',
            'Caras',
            'Acabado',
            'Número de cambios',
            'Hojas',
            'H/A',
            'P/F',
            'Total de hojas',
            'Tamaño de impresión',
            'Total tamaño de impresión',
            'Última modificación',
            'Modificado por'
        ];
    }

    public function map($production): array
    {
        // Obtener cantidad de parcial 1
        $partial1 = $production->partials[0]['quantity'] ?? 0;
        // Obtener cantidad de parcial N que es la suma de todos los parciales menos el primero
        $partialN = $production->partials ? array_sum(array_column($production->partials, 'quantity')) - $partial1 : 0;

        // (Cálculo de mermas y porcentajes por estación) ---
        $productionScrapPercentage = $this->getPercentage($production->production_scrap, $production->quantity);
        $qualityScrapPercentage = $this->getPercentage($production->quality_scrap, $production->close_quantity);
        $inspectionScrapPercentage = $this->getPercentage($production->inspection_scrap, $production->quality_quantity);

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
            $production->material . ' ' . $production->gauge . ' ' . $production->varnish_type,
            $production->width . ' x ' . $production->large,
            $production->ts,
            $production->start_date?->isoFormat('YYYY/MM/DD'),
            $production->estimated_date?->isoFormat('DD/MM/YYYY'),
            $production->close_production_date?->toDateTimeString(),
            $production->close_quantity,
            // $production->production_scrap,
            // number_format($productionScrapPercentage, 2) . '%',
            // $production->production_shortage,
            $production->quality_quantity,
            $production->quality_scrap,
            number_format($qualityScrapPercentage, 2) . '%',
            $production->quality_shortage,
            $production->current_quantity,
            $production->inspection_scrap,
            number_format($inspectionScrapPercentage, 2) . '%',
            $production->inspection_shortage,
            $production->scrap_quantity,
            $production->shortage_quantity,
            $production->quantity - $production->current_quantity,
            $production->estimated_package_date?->isoFormat('DD/MM/YYYY'),
            $production->finish_date?->toDateTimeString(),
            $partial1,
            $partialN,
            $production->dfi,
            $production->pps,
            $production->adjust,
            $production->faces,
            $production->look,
            $production->changes,
            $production->sheets,
            $production->ha,
            $production->pf,
            $production->ts,
            $production->ps,
            $production->tps,
            $production->updated_at?->toDateTimeString(),
            $production->modifiedUser->name ?? ''
        ];
    }
}
