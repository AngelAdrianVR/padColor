<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductSheetExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $product;
    protected $sheetStructure;

    public function __construct(Product $product, $sheetStructure)
    {
        $this->product = $product;
        $this->sheetStructure = $sheetStructure;
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function view(): View
    {
        return view('exports.product_sheet', [
            'product' => $this->product,
            'sheetStructure' => $this->sheetStructure
        ]);
    }

    /**
     * Aplica estilos a la hoja de cálculo.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para las cabeceras de Pestaña (ej. Fila 1)
            'A1'    => ['font' => ['bold' => true, 'size' => 16]],
            'A'     => ['font' => ['bold' => true]], // Estilo para toda la columna A (labels)
        ];
    }
}
