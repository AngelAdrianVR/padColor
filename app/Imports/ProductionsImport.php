<?php

namespace App\Imports;

use App\Models\Production;
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
            BeforeImport::class => function(BeforeImport $event) {
                Log::info('[EXCEL] Iniciando importación de archivo');
            },
            AfterImport::class => function(AfterImport $event) {
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
        if (!isset($row['codigo'], $row['almacen'], $row['es'])) {
            // Log::warning("[EXCEL] Fila {$this->processedRows} omitida - Faltan campos requeridos", $row);
            Log::warning("[EXCEL] Fila {$this->processedRows} omitida - Faltan campos requeridos");
            return null;
        }

        try {
            $codigo = $row['codigo'];
            $almacen = $row['almacen'];
            $esEntrada = strtoupper($row['es']) === 'E';
            $cantidad = $esEntrada ? $row['entrada'] : $row['salida'];

            // Log::debug("[EXCEL] Procesando: Código {$codigo}, Almacén {$almacen}, Tipo " . ($esEntrada ? 'Entrada' : 'Salida'));

            // Buscar o crear el registro
            $production = Production::firstOrNew(['folio' => $codigo]);

            if (!$production->exists) {
                $this->createdRecords++;
                Log::info("[EXCEL] Nuevo registro creado para código {$codigo}");
            } elseif ($production->current_quantity != $cantidad || $production->station != $almacen) {
                $this->updatedRecords++;
                Log::info("[EXCEL] Actualizando registro existente para código {$codigo}");
            }

            if (!$production->exists) {
                $production->fill([
                    'client' => 'JOSEFINA ISABEL CICERO FERNANDEZ',
                    'quantity' => 0,
                    'current_quantity' => $cantidad,
                    'station' => $almacen,
                    'product_id' => 1,
                    'machine_id' => 1,
                    'user_id' => auth()->id(),
                    'modified_user_id' => auth()->id(),
                ])->save();
            } elseif ($production->current_quantity != $cantidad || $production->station != $almacen) {
                $production->current_quantity = $cantidad;
                $production->station = $almacen;
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