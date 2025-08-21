<?php

namespace App\Exports;

use App\Models\Import;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class ImportsReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $imports;

    public function __construct($imports)
    {
        $this->imports = $imports;
    }

    public function collection()
    {
        return $this->imports;
    }

    public function styles(Worksheet $sheet)
    {
        // Poner en negrita la fila de encabezados
        $sheet->getStyle(1)->getFont()->setBold(true);

        // Autoajustar el ancho de todas las columnas
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        return [];
    }

    public function headings(): array
    {
        return [
            'Folio',
            'Proveedor',
            'Fecha Creación',
            'Fecha Embarque',
            'Fecha Llegada',
            'Fecha Entrega Bodega',
            'Días con Proveedor',
            'Días en Tránsito',
            'Días en Destino',
            'Días Totales',
            'Costo Total Mercancía (USD)',
            // 'Costo Total Logística (USD)',
            // 'Costo Total Impuestos (USD)',
            'Otros Costos (USD)',
            'Gran Total (USD)',
        ];
    }

    /**
     * @param Import $import
     */
    public function map($import): array
    {
        // --- Cálculo de Tiempos ---
        $createdAt = Carbon::parse($import->created_at);
        $shipDate = $import->estimated_ship_date ? Carbon::parse($import->estimated_ship_date) : null;
        $arrivalDate = $import->estimated_arrival_date ? Carbon::parse($import->estimated_arrival_date) : null;
        $deliveryDate = $import->warehouse_delivery_date ? Carbon::parse($import->warehouse_delivery_date) : null;

        $daysWithSupplier = $shipDate ? $createdAt->diffInDays($shipDate) : 'N/A';
        $daysInTransit = $shipDate && $arrivalDate ? $shipDate->diffInDays($arrivalDate) : 'N/A';
        $daysInDestination = $arrivalDate && $deliveryDate ? $arrivalDate->diffInDays($deliveryDate) : 'N/A';
        $totalDays = $deliveryDate ? $createdAt->diffInDays($deliveryDate) : 'N/A';

        // --- Cálculo de Costos ---
        // (Nota: Se asume una tasa de conversión estática. En una app real, esto debería ser dinámico)
        $exchangeRate = 19.0; 

        $merchandiseCost = $import->rawMaterials->reduce(function ($carry, $item) {
            return $carry + ($item->pivot->quantity * $item->pivot->unit_cost);
        }, 0);

        $logisticsCost = 0;
        $taxesCost = 0;
        $otherCosts = 0;

        foreach ($import->costs as $cost) {
            $amountInUSD = $cost->currency === 'MXN' ? $cost->amount / $exchangeRate : $cost->amount;
            
            if (stripos($cost->concept, 'flete') !== false || stripos($cost->concept, 'seguro') !== false) {
                $logisticsCost += $amountInUSD;
            } elseif (stripos($cost->concept, 'impuesto') !== false || stripos($cost->concept, 'arancel') !== false) {
                $taxesCost += $amountInUSD;
            } else {
                $otherCosts += $amountInUSD;
            }
        }
        
        // $grandTotal = $merchandiseCost + $logisticsCost + $taxesCost + $otherCosts;
        $grandTotal = $merchandiseCost + $otherCosts;

        return [
            $import->id,
            $import->supplier->name ?? 'N/A',
            $createdAt->format('Y-m-d'),
            $shipDate ? $shipDate->format('Y-m-d') : 'N/A',
            $arrivalDate ? $arrivalDate->format('Y-m-d') : 'N/A',
            $deliveryDate ? $deliveryDate->format('Y-m-d') : 'N/A',
            $daysWithSupplier,
            $daysInTransit,
            $daysInDestination,
            $totalDays,
            number_format($merchandiseCost, 2),
            // number_format($logisticsCost, 2),
            // number_format($taxesCost, 2),
            number_format($otherCosts, 2),
            number_format($grandTotal, 2),
        ];
    }
}
