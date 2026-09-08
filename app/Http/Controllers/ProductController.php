<?php

namespace App\Http\Controllers;

use App\Models\ChangeRequest;
use App\Models\NotificationEvent;
use App\Models\Product;
use App\Models\ProductSheetField;
use App\Models\ProductSheetTab;
use App\Models\User;
use App\Notifications\BasicNotification;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use App\Exports\ProductSheetExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index()
    {
        return inertia('Product/Index', [
            'products' => Product::with('media')->latest('id')->paginate(30)
        ]);
    }

    public function show(Product $product)
    {
        $user = Auth::user();
        $product->load('media');

        // Si sheet_data está vacío, lo inicializamos con los tipos de datos correctos.
        if (empty($product->sheet_data)) {
            // 1. Obtener todos los campos activos con su slug y tipo.
            $allFields = ProductSheetField::where('is_active', true)->get(['slug', 'type']);

            // 2. Crear un objeto donde cada slug es una clave y el valor depende del tipo.
            $initializedData = $allFields->reduce(function ($carry, $field) {
                // Los campos que manejan múltiples valores (o archivos) deben ser arrays.
                $multiValueTypes = ['multicheckbox', 'checklist', 'file'];

                if (in_array($field->type, $multiValueTypes)) {
                    $carry[$field->slug] = []; // Inicializar como array vacío.
                } else {
                    $carry[$field->slug] = null; // Inicializar como nulo para los demás.
                }
                return $carry;
            }, []);

            $product->sheet_data = $initializedData;
        }

        // 1. Obtener TODA la estructura de pestañas.
        $allSheetStructure = ProductSheetTab::with(['fields.options' => fn($q) => $q->orderBy('order')])
            ->where('is_active', true)->orderBy('order')->get()
            ->map(function ($tab) {
                $activeFields = $tab->fields->where('is_active', true);
                $tab->fields_by_section = $activeFields->groupBy('section');
                unset($tab->fields);
                return $tab;
            });

        // 2. Filtrar la estructura basándose en los permisos del usuario.
        $sheetStructure = $allSheetStructure->filter(function ($tab) use ($user) {
            return $user->can('Ver información de ' . strtolower($tab->name) . ' en fichas técnicas');
        })->values();

        // 3. Comprobar el permiso para el historial.
        $canViewHistory = $user->can('Ver historial en fichas técnicas');

        // --- LÓGICA DE HISTORIAL ACTUALIZADA ---
        $changeRequestHistory = ChangeRequest::where('product_id', $product->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->with(['requester', 'approver', 'reviewers', 'media'])
            ->latest('decided_at')->get()
            ->map(function ($request) use ($sheetStructure) {
                // Ahora comparamos los datos de la solicitud con su PROPIA copia de los datos antiguos.
                $historicalChanges = $this->prepareChangeList(
                    $request->previous_data ?? [], // <-- USA LOS DATOS ANTIGUOS GUARDADOS
                    $request->data,
                    $sheetStructure
                );
                return [
                    'id' => $request->id,
                    'status' => $request->status,
                    'requester' => $request->requester,
                    'approver' => $request->approver,
                    'created_at' => $request->created_at,
                    'decided_at' => $request->decided_at,
                    'requester_comments' => $request->comments,
                    'reviewers' => $request->reviewers->map(fn($r) => ['name' => $r->name, 'status' => $r->pivot->status, 'comments' => $r->pivot->comments]),
                    'changes' => $historicalChanges,
                    'attached_media' => $request->getMedia('pending_documents')->map(fn($m) => ['name' => $m->file_name, 'size' => $m->size, 'url' => $m->getUrl()]),
                ];
            });

        // La lógica para la solicitud pendiente se mantiene igual, comparando con el producto actual.
        $pendingChangeRequest = ChangeRequest::where('product_id', $product->id)->where('status', 'pending')
            ->with(['requester', 'reviewers', 'media'])->first();

        $changeDetails = null;
        if ($pendingChangeRequest) {
            $changes = $this->prepareChangeList($product->sheet_data ?? [], $pendingChangeRequest->data, $sheetStructure);
            $isReviewer = Auth::check() ? $pendingChangeRequest->reviewers->contains(Auth::user()) : false;
            $currentUserVote = $isReviewer ? $pendingChangeRequest->reviewers()->where('user_id', Auth::id())->first()->pivot : null;
            $changeDetails = [
                'id' => $pendingChangeRequest->id,
                'requester_name' => $pendingChangeRequest->requester->name,
                'created_at' => $pendingChangeRequest->created_at,
                'requester_comments' => $pendingChangeRequest->comments,
                'reviewers' => $pendingChangeRequest->reviewers->map(fn($r) => ['name' => $r->name, 'status' => $r->pivot->status, 'comments' => $r->pivot->comments]),
                'pending_media' => $pendingChangeRequest->getMedia('pending_documents')->map(fn($m) => ['name' => $m->file_name, 'size' => $m->size, 'url' => $m->getUrl()]),
                'changes' => $changes,
                'is_reviewer' => $isReviewer,
                'current_user_vote_status' => $currentUserVote ? $currentUserVote->status : null,
            ];
        }

        return Inertia::render('Product/Show', [
            'product' => $product,
            'sheetStructure' => $sheetStructure,
            'canViewHistory' => $canViewHistory,
            'pendingChangeRequest' => $changeDetails,
            'changeRequestHistory' => $changeRequestHistory,
        ]);
    }

    /**
     * Helper para comparar datos y generar una lista con contexto de pestaña/sección.
     */
    private function prepareChangeList(array $oldData, array $newData, $sheetStructure)
    {
        $fieldMap = collect();
        foreach ($sheetStructure as $tab) {
            if (empty($tab->fields_by_section)) continue;
            foreach ($tab->fields_by_section as $sectionName => $fields) {
                foreach ($fields as $field) {
                    $fieldData = $field->toArray();
                    $fieldData['tab_name'] = $tab->name;
                    $fieldData['section_name'] = $this->formatSectionName($sectionName);
                    $fieldMap->put($field->slug, $fieldData);
                }
            }
        }

        $allKeys = array_unique(array_merge(array_keys($oldData), array_keys($newData)));

        $changes = [];
        foreach ($allKeys as $key) {
            $oldValue = Arr::get($oldData, $key);
            $newValue = Arr::get($newData, $key);
            $oldString = is_array($oldValue) ? json_encode(Arr::sort($oldValue)) : (string) $oldValue;
            $newString = is_array($newValue) ? json_encode(Arr::sort($newValue)) : (string) $newValue;
            if ($oldString !== $newString) {
                $fieldInfo = $fieldMap->get($key);
                $label = $fieldInfo['label'] ?? str_replace('_', ' ', ucfirst($key));
                $tabName = $fieldInfo['tab_name'] ?? 'General';
                $sectionName = $fieldInfo['section_name'] ?? 'N/A';

                // Comprobar si el campo es de tipo 'file' para manejarlo de forma especial.
                if ($fieldInfo && $fieldInfo['type'] === 'file') {
                    $oldDisplay = !empty($oldValue) ? '[Archivo(s) existente(s)]' : 'Vacío';
                    $newDisplay = !empty($newValue) ? '[Nuevos archivo(s) adjunto(s)]' : 'Sin archivos';
                }
                // Manejar campos con opciones (select, radio, etc.)
                else if ($fieldInfo && !empty($fieldInfo['options'])) {
                    $options = collect($fieldInfo['options']);
                    $getLabels = function ($values) use ($options) {
                        if (empty($values)) return 'Vacío';
                        return collect($values)->map(function ($value) use ($options) {
                            return $options->firstWhere('value', $value)['label'] ?? $value;
                        })->implode(', ');
                    };
                    $oldDisplay = $getLabels(Arr::wrap($oldValue));
                    $newDisplay = $getLabels(Arr::wrap($newValue));
                }
                // Manejar todos los demás tipos de campos (texto, textarea, etc.)
                else {
                    $oldDisplay = !is_null($oldValue) && $oldValue !== '' ? (is_array($oldValue) ? implode(', ', $oldValue) : $oldValue) : 'Vacío';
                    $newDisplay = !is_null($newValue) && $newValue !== '' ? (is_array($newValue) ? implode(', ', $newValue) : $newValue) : 'Vacío';
                }

                $changes[] = [
                    'tab' => $tabName,
                    'section' => $sectionName,
                    'label' => $label,
                    'old' => $oldDisplay,
                    'new' => $newDisplay,
                ];
            }
        }
        return $changes;
    }

    private function formatSectionName($slug)
    {
        if (!$slug) return '';
        return str_replace('_', ' ', ucfirst($slug));
    }

    public function updateSheetData(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sheet_data' => 'nullable|array',
            'comments' => 'nullable|string|max:2000',
        ]);

        // --- LÓGICA REFACTORIZADA PARA OBTENER Y SEPARAR DATOS ---
        $sheetDataInput = $request->input('sheet_data', []);
        $sheetFilesInput = $request->file('sheet_data', []);

        // Si no se envían archivos, $sheetFilesInput puede ser null. Lo convertimos a array.
        if (is_null($sheetFilesInput)) {
            $sheetFilesInput = [];
        }

        // Se usa array_replace_recursive para que los archivos reales sobrescriban
        // cualquier placeholder (ej. null) que pueda venir del formulario.
        $mergedSheetData = array_replace_recursive($sheetDataInput, $sheetFilesInput);

        $dataForJson = [];
        $filesForMedia = [];

        foreach ($mergedSheetData as $slug => $value) {
            // Comprueba si el valor es un array que contiene un objeto UploadedFile.
            if (is_array($value) && !empty($value) && $value[0] instanceof UploadedFile) {
                $filesForMedia[$slug] = $value;
            } else {
                $dataForJson[$slug] = $value;
            }
        }

        // --- INICIO DE LA LÓGICA CONDICIONAL ---
        $isFirstTimeSave = empty($product->sheet_data);

        if ($isFirstTimeSave) {
            // --- FLUJO DE GUARDADO DIRECTO (SIN APROBACIÓN) ---
            $product->sheet_data = $dataForJson;
            $product->save();

            foreach ($filesForMedia as $slug => $files) {
                foreach ($files as $file) {
                    $product->addMedia($file)->toMediaCollection($slug);
                }
            }

            return to_route('products.show', $product)->with('success', 'Ficha técnica guardada correctamente.');
        } else {
            // --- FLUJO DE SOLICITUD DE CAMBIO (EXISTENTE) ---
            $permissionName = 'Revisar cambios de fichas técnicas';
            $reviewerUsers = User::permission($permissionName)->get();
            if ($reviewerUsers->isEmpty()) {
                return back()->with('error', 'No se pudo crear la solicitud. No hay revisores asignados.');
            }

            $changeRequest = ChangeRequest::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'previous_data' => $product->sheet_data ?? [],
                'data' => $dataForJson,
                'comments' => $validated['comments'] ?? null,
                'status' => 'pending',
            ]);

            foreach ($filesForMedia as $slug => $files) {
                foreach ($files as $file) {
                    $changeRequest->addMedia($file)
                        ->withCustomProperties(['field_slug' => $slug])
                        ->toMediaCollection('pending_documents');
                }
            }

            $changeRequest->reviewers()->attach($reviewerUsers->pluck('id'));
            $requester = Auth::user();
            $subject = "Nueva solicitud de cambio para \"{$product->name}\"";
            $description = "ha solicitado tu revisión para cambios en la ficha técnica del producto.";
            $url = route('products.show', $product);
            $notificationInstance = new BasicNotification($subject, $description, $requester->name, $requester->profile_photo_url, $url);
            Notification::send($reviewerUsers, $notificationInstance);

            return to_route('products.show', $product)->with('success', 'Solicitud de cambio enviada para aprobación.');
        }
    }

    public function create()
    {
        return inertia('Product/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'nullable|string|max:255',
            'season' => 'required|string|max:255',
            'material' => 'nullable|string|max:255',
            'stock' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'created_at' => 'nullable|date',
        ]);

        $product = Product::create($request->all());

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('image');
        }

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $uploadedFile) {
                $product->addMedia($uploadedFile)->toMediaCollection('files');
            }
        }

        return to_route('products.show', $product->id);
    }

    public function edit(Product $product)
    {
        $product->load('media');
        return inertia('Product/Edit', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'code' => 'nullable|string|max:255',
            'season' => 'required|string|max:255',
            'material' => 'nullable|string|max:255',
            'stock' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'created_at' => 'nullable|date',
        ]);

        $product->update($request->all());

        if ($request->imageCleared) {
            $product->clearMediaCollection('image');
        }

        return to_route('products.show', $product->id);
    }

    public function updateWithMedia(Request $request, Product $product)
    {
        $request->validate([
            'code' => 'nullable|string|max:255',
            'season' => 'required|string|max:255',
            'material' => 'nullable|string|max:255',
            'stock' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'created_at' => 'nullable|date',
        ]);

        $product->update($request->all());

        if ($request->hasFile('image')) {
            $product->clearMediaCollection('image');
            $product->addMediaFromRequest('image')->toMediaCollection('image');
        }

        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $uploadedFile) {
                $product->addMedia($uploadedFile)->toMediaCollection('files');
            }
        }

        return to_route('products.show', $product->id);
    }

    public function destroy(Product $product)
    {
        $product->delete();
    }

    public function clone(Product $product)
    {
        $clonedProduct = $product->replicate();
        $clonedProduct->name = $product->name . ' (Copia)';
        $clonedProduct->save();

        foreach ($product->media as $mediaItem) {
            $mediaItem->copy($clonedProduct, $mediaItem->collection_name);
        }
    }

    public function getAll()
    {
        $items = Product::latest('id')->get(['name', 'id', 'material'])->take(100);
        return response()->json(compact('items'));
    }

    // para el listado en creación de producciones / importaciones (búsqueda remota de productos)
    public function getMatch($query)
    {
        $items = Product::latest('id')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%");
            })
            ->get(['name', 'id', 'code', 'material'])
            ->take(30);
        return response()->json(compact('items'));
    }

    public function getMatches($query)
    {
        $products = Product::where(function ($q) use ($query) {
            $q->where('code', 'like', "%{$query}%")
                ->orWhere('name', 'like', "%{$query}%")
                ->orWhere('season', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%");
        })
            ->paginate(200);

        return response()->json(['items' => $products], 200);
    }

    public function exportSheet(Product $product)
    {
        $user = Auth::user();

        // Obtener la estructura completa de la ficha.
        $allSheetStructure = ProductSheetTab::with(['fields.options' => fn($q) => $q->orderBy('order')])
            ->where('is_active', true)->orderBy('order')->get()
            ->map(function ($tab) {
                $activeFields = $tab->fields->where('is_active', true);
                $tab->fields_by_section = $activeFields->groupBy('section');
                unset($tab->fields);
                return $tab;
            });

        // Filtrar la estructura basándose en los permisos del usuario actual.
        $sheetStructure = $allSheetStructure->filter(function ($tab) use ($user) {
            return $user->can('Ver información de ' . strtolower($tab->name) . ' en fichas técnicas');
        })->values();

        $fileName = 'ficha-tecnica-' . \Illuminate\Support\Str::slug($product->name) . '.xlsx';

        return Excel::download(new ProductSheetExport($product, $sheetStructure), $fileName);
    }

    /**
     * Recibe un lote de filas de Excel (ya normalizadas en el frontend) y las
     * inserta/actualiza en la tabla products. Cada petición maneja solo un lote,
     * por lo que no depende de colas ni de límites de ejecución del servidor.
     */
    public function importBatch(Request $request)
    {
        $request->validate([
            'rows' => 'required|array|min:1|max:1000',
        ]);

        $rows = $request->input('rows', []);
        $created = 0;
        $updated = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $row) {
                $code = $this->cleanExcelString($row['codigo'] ?? null);
                $name = $this->cleanExcelString($row['descripcion'] ?? null);
                $excelRow = $row['excel_row'] ?? null;

                // Fila completamente vacía: se ignora sin marcar error.
                if ($name === null && $code === null) {
                    continue;
                }

                // La columna "Descripción" se usa como nombre y es obligatoria.
                if ($name === null) {
                    $errors[] = ['fila' => $excelRow, 'message' => 'Falta el nombre del producto (columna "Descripción").'];
                    continue;
                }

                $season = $this->cleanExcelString($row['familia'] ?? null) ?? 'Toda ocasión';
                $stock = $this->parseExcelNumber($row['existencia_actual'] ?? null);
                $price = $this->parseExcelNumber($row['precio_de_lista'] ?? null);
                $measureUnit = $this->cleanExcelString($row['unidad'] ?? null);

                $data = [
                    'name' => mb_substr($name, 0, 255),
                    'code' => $code !== null ? mb_substr($code, 0, 255) : null,
                    'description' => $this->cleanExcelString($row['descripcion_alterna'] ?? null),
                    'measure_unit' => $measureUnit !== null ? mb_substr($measureUnit, 0, 255) : null,
                    'season' => mb_substr($season, 0, 255),
                    'stock' => $stock !== null && $stock >= 0 ? $stock : null,
                    'price' => $price !== null && $price >= 0 ? $price : null,
                ];

                // "Medida" con formato "Ancho x Largo" (ej. "20 x 30").
                $dimensions = $this->parseDimensions($row['medida'] ?? null);
                if ($dimensions !== null) {
                    $data['width'] = $dimensions['width'];
                    $data['large'] = $dimensions['large'];
                }

                if ($code !== null) {
                    // Si ya existe un producto con ese código, se actualiza en lugar de duplicarse.
                    $product = Product::updateOrCreate(['code' => $code], $data);
                } else {
                    $product = Product::create($data);
                }

                if ($product->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[PRODUCTOS IMPORT] Error procesando lote: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'message' => 'No se pudo procesar el lote. Ningún registro de este lote fue guardado.',
                'errors' => [['fila' => null, 'message' => $e->getMessage()]],
            ], 422);
        }

        return response()->json(compact('created', 'updated', 'errors'));
    }

    /**
     * Limpia un valor de celda: recorta espacios y convierte vacíos en null.
     */
    private function cleanExcelString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = trim((string) $value);

        return $clean === '' ? null : $clean;
    }

    /**
     * Convierte un valor de celda a número tolerando formatos comunes:
     * "1,234.56", "1.234,56", "12,50", "$1 250.00", etc.
     */
    private function parseExcelNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $number = str_replace(['$', ' '], '', trim((string) $value));

        if ($number === '' || !preg_match('/^-?[\d.,]+$/', $number)) {
            return null;
        }

        $commaPos = strrpos($number, ',');
        $dotPos = strrpos($number, '.');

        if ($commaPos !== false && $dotPos !== false) {
            // El separador que aparece al final es el decimal.
            $number = $commaPos > $dotPos
                ? str_replace(['.', ','], ['', '.'], $number) // 1.234,56
                : str_replace(',', '', $number);              // 1,234.56
        } elseif ($commaPos !== false) {
            // Una sola coma al final es decimal (12,50); si no, son miles (1,234).
            $number = substr_count($number, ',') === 1 && preg_match('/,\d{1,2}$/', $number)
                ? str_replace(',', '.', $number)
                : str_replace(',', '', $number);
        }

        return is_numeric($number) ? (float) $number : null;
    }

    /**
     * Intenta interpretar "Medida" como "Ancho x Largo" (ej. "20 x 30").
     */
    private function parseDimensions($value): ?array
    {
        $measure = $this->cleanExcelString($value);

        if ($measure === null || !preg_match('/^\s*(\d+(?:[.,]\d+)?)\s*[xX×]\s*(\d+(?:[.,]\d+)?)\s*$/', $measure, $matches)) {
            return null;
        }

        $width = (int) round($this->parseExcelNumber($matches[1]) ?? 0);
        $large = (int) round($this->parseExcelNumber($matches[2]) ?? 0);

        if ($width <= 0 || $large <= 0 || $width > 65535 || $large > 65535) {
            return null;
        }

        return ['width' => $width, 'large' => $large];
    }
}
