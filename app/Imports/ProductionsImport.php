<?php

namespace App\Imports;

use App\Models\Machine;
use App\Models\Product;
use App\Models\Production;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Support\Facades\Log;

class ProductionsImport implements ToModel, WithHeadingRow, WithEvents
{
    private $processedRows = 0;
    private $createdRecords = 0;
    private $updatedRecords = 0;

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                Log::info('[EXCEL] Iniciando importación de archivo');
            },
            AfterImport::class => function (AfterImport $event) {
                Log::info("[EXCEL] Resumen de importación: 
                    Filas procesadas: {$this->processedRows}
                    Registros creados: {$this->createdRecords}
                    Registros actualizados: {$this->updatedRecords}");
            },
        ];
    }

    public function model(array $row)
    {
        $this->processedRows++;

        // Validación de columnas requeridas
        // if (!isset($row['n_orden'], $row['progreso'], $row['cantidad_final'])) {
        //     // Log::warning("[EXCEL] Fila {$this->processedRows} omitida - Faltan campos requeridos", $row);
        //     Log::warning("[EXCEL] Fila {$this->processedRows} omitida - Faltan campos requeridos");
        //     return null;
        // }

        try {
            $n_orden = $row['n_orden'];
            $progreso = $row['progreso'];

            // Log::debug("[EXCEL] Procesando: Código {$n_orden}, Almacén {$progreso}, Tipo " . ($esEntrada ? 'Entrada' : 'Salida'));

            // Buscar o crear el registro
            $production = Production::firstOrNew(['folio' => $n_orden]);

            if (!$production->exists) {
                $this->createdRecords++;
                Log::info("[EXCEL] Nuevo registro creado para código {$n_orden}");
            } elseif ($production->current_quantity != $row['cantidad_final'] || $production->station != $progreso) {
                $this->updatedRecords++;
                Log::info("[EXCEL] Actualizando registro existente para código {$n_orden}");
            }

            //buscar maquina por nombre
            $machine = Machine::where('name', $row['maquina'])->first();
            if (!$production->exists) {
                // buscar el producto por nombre
                $product = Product::where('name', $row['producto'])->first();

                //medidas
                $dfi = str_replace(' ', '', $row['medida']);
                $width = explode('x', $dfi)[0];
                $large = explode('x', $dfi)[1];

                // Procesar fechas
                $startDate = Carbon::createFromFormat('d/m/Y', trim($row['fecha_inicio']))->format('Y-m-d');
                $estimatedDate = !empty(trim($row['fecha_esperada_produccion'])) ?
                    Carbon::createFromFormat('d/m/Y', trim($row['fecha_esperada_produccion']))->format('Y-m-d') :
                    null;
                $estimatedPackageDate = !empty(trim($row['fecha_esperada_empaque'])) ?
                    Carbon::createFromFormat('d/m/Y', trim($row['fecha_esperada_empaque']))->format('Y-m-d') :
                    null;
                $closeProductionDate = !empty(trim($row['fecha_fin_produccion'])) ?
                    Carbon::createFromFormat('d/m/Y', trim($row['fecha_fin_produccion']))->format('Y-m-d') :
                    null;
                $finishDate = !empty(trim($row['producto_terminado'])) ?
                    Carbon::createFromFormat('d/m/Y', trim($row['producto_terminado']))->format('Y-m-d') :
                    null;

                // Procesar partials (array o null)
                $partials = null;
                $partial1 = !empty(trim($row['parcial_1'])) ? (float)trim($row['parcial_1']) : 0;

                if ($partial1 > 0) {
                    $partials = [
                        [
                            'quantity' => $partial1,
                            'date' => $startDate
                        ]
                    ];

                    // Agregar partialn si existe
                    $partialn = !empty(trim($row['parcial_n'])) ? (float)trim($row['parcial_n']) : 0;
                    if ($partialn > 0) {
                        $partials[] = [
                            'quantity' => $partialn,
                            'date' => $startDate
                        ];
                    }
                }

                $production->fill([
                    'client' => $row['cliente'],
                    'quantity' => $row['cantidad_programada'],
                    'current_quantity' => $row['cantidad_final'],
                    'notes' => $row['notas'],
                    'materials' => [$row['lista_material']],
                    'material' => $row['material'],
                    'dfi' => $row['medida'],
                    'width' => $width,
                    'large' => $large,
                    'ts' => $row['total_hojas'],
                    'start_date' => $startDate,
                    'estimated_date' => $estimatedDate,
                    'close_production_date' => $closeProductionDate,
                    'estimated_package_date' => $estimatedPackageDate,
                    'finish_date' => $finishDate,
                    'close_quantity' => $row['cantidad_entregada'],
                    'current_quantity' => $row['cantidad_final'],
                    'station' => $progreso,
                    'partials' => $partials,
                    'product_id' => $product->id ?? 1,
                    'machine_id' => $machine->id ?? 28,
                    'user_id' => auth()->id(),
                    'modified_user_id' => auth()->id(),
                ])->save();
            } elseif ($production->current_quantity != $row['cantidad_final'] || $production->station != $progreso || $production->machine_id != $machine->id) {
                // Actualizar el registro existente
                $production->current_quantity = $row['cantidad_final'];
                $production->station = $progreso;
                $production->machine_id = $machine->id ?? 28;
                $production->modified_user_id = auth()->id();
                $production->save();
            }

            return null; // No necesitamos retornar el modelo ya que lo manejamos directamente

        } catch (\Exception $e) {
            Log::error("[EXCEL] Error procesando fila {$this->processedRows}: " . $e->getMessage(), [
                'row' => $row,
                'error' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function headingRow(): int
    {
        return 1; // La primera fila contiene los encabezados
    }

    public function batchSize(): int
    {
        return 500; // Procesar en lotes de 500 registros
    }

    public function chunkSize(): int
    {
        return 500; // Leer el archivo en trozos de 500 registros
    }
}
