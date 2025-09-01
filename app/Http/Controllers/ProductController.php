<?php

namespace App\Http\Controllers;

use App\Models\ChangeRequest;
use App\Models\Product;
use App\Models\ProductSheetTab;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

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
        $product->load('media');

        $sheetStructure = ProductSheetTab::with(['fields.options' => function ($query) {
            $query->orderBy('order');
        }])->where('is_active', true)->orderBy('order')->get()->map(function ($tab) {
            $activeFields = $tab->fields->where('is_active', true);
            $tab->fields_by_section = $activeFields->groupBy('section');
            unset($tab->fields);
            return $tab;
        });

        $pendingChangeRequest = ChangeRequest::where('product_id', $product->id)
            ->where('status', 'pending')
            ->with(['requester', 'reviewers', 'media'])
            ->first();

        $changeDetails = null;
        if ($pendingChangeRequest) {
            $changes = $this->prepareChangeList($product, $pendingChangeRequest, $sheetStructure);
            
            $isReviewer = Auth::check() ? $pendingChangeRequest->reviewers->contains(Auth::user()) : false;
            
            // Busca el voto específico del usuario actual en la tabla pivote.
            $currentUserVote = $isReviewer ? $pendingChangeRequest->reviewers()->where('user_id', Auth::id())->first()->pivot : null;

            $changeDetails = [
                'id' => $pendingChangeRequest->id,
                'requester_name' => $pendingChangeRequest->requester->name,
                'requester_comments' => $pendingChangeRequest->comments,
                'created_at' => $pendingChangeRequest->created_at,
                'reviewers' => $pendingChangeRequest->reviewers->map(fn ($reviewer) => [
                    'name' => $reviewer->name,
                    'status' => $reviewer->pivot->status,
                    'comments' => $reviewer->pivot->comments,
                ]),
                'pending_media' => $pendingChangeRequest->getMedia('pending_documents')->map(fn ($media) => [
                    'name' => $media->file_name, 'size' => $media->size, 'url' => $media->getUrl()
                ]),
                'changes' => $changes,
                'is_reviewer' => $isReviewer,
                'current_user_vote_status' => $currentUserVote ? $currentUserVote->status : null, // <-- NUEVO
            ];
        }

        return Inertia::render('Product/Show', [
            'product' => $product,
            'sheetStructure' => $sheetStructure,
            'pendingChangeRequest' => $changeDetails,
        ]);
    }

    /**
     * Helper para comparar datos y generar una lista con contexto de pestaña/sección.
     */
    private function prepareChangeList($product, $changeRequest, $sheetStructure)
    {
        // 1. Construir un mapa de campos (fieldMap) robusto y detallado.
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
        
        // 2. Comparar los datos viejos y nuevos.
        $oldData = $product->sheet_data ?? [];
        $newData = $changeRequest->data;
        $allKeys = array_unique(array_merge(array_keys($oldData), array_keys($newData)));
        
        $changes = [];

        foreach ($allKeys as $key) {
            $oldValue = Arr::get($oldData, $key);
            $newValue = Arr::get($newData, $key);

            // Normalizar valores para una comparación consistente
            $oldString = is_array($oldValue) ? json_encode(Arr::sort($oldValue)) : (string) $oldValue;
            $newString = is_array($newValue) ? json_encode(Arr::sort($newValue)) : (string) $newValue;

            if ($oldString !== $newString) {
                // La búsqueda en $fieldMap ahora será exitosa.
                $fieldInfo = $fieldMap->get($key);

                $label = $fieldInfo['label'] ?? str_replace('_', ' ', ucfirst($key));
                $tabName = $fieldInfo['tab_name'] ?? 'General';
                $sectionName = $fieldInfo['section_name'] ?? 'N/A';
                
                // Convertir valores de 'value' a 'label' para que sean legibles si hay opciones.
                if ($fieldInfo && !empty($fieldInfo['options'])) {
                    $options = collect($fieldInfo['options']);
                    // Función auxiliar para buscar etiquetas
                    $getLabels = function($values) use ($options) {
                        if (empty($values)) return 'Vacío';
                        return collect($values)->map(function($value) use ($options) {
                            return $options->firstWhere('value', $value)['label'] ?? $value;
                        })->implode(', ');
                    };
                    
                    $oldDisplay = $getLabels(Arr::wrap($oldValue));
                    $newDisplay = $getLabels(Arr::wrap($newValue));
                } else {
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

    /**
     * Formatea un slug a un nombre legible.
     */
    private function formatSectionName($slug)
    {
        if (!$slug) return '';
        return str_replace('_', ' ', ucfirst($slug));
    }

    public function updateSheetData(Request $request, Product $product)
    {
        // 1. Validar los datos entrantes, incluyendo los archivos.
        $validated = $request->validate([
            'sheet_data' => 'nullable|array',
            'comments' => 'nullable|string',
            'new_documents' => 'nullable|array',
            'new_documents.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,dwg,step|max:10240' // max 10MB
        ]);

        // 2. Crear la solicitud de cambio con los datos validados.
        $changeRequest = ChangeRequest::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(), // El usuario que está haciendo la solicitud.
            'data' => $validated['sheet_data'] ?? [], // Guarda los datos del formulario en el campo 'data'.
            'status' => 'pending',
            'comments' => $validated['comments'],
        ]);

        // 3. Si hay nuevos documentos, adjuntarlos a la SOLICITUD DE CAMBIO (no al producto).
        if ($request->hasFile('new_documents')) {
            foreach ($request->file('new_documents') as $document) {
                $changeRequest->addMedia($document)->toMediaCollection('pending_documents');
            }
        }

        // 4. (Opcional) Asignar revisores automáticamente.
        $reviewers = User::whereIn('id', [3, 4, 5])->pluck('id'); // Ejemplo simple
        $changeRequest->reviewers()->attach($reviewers);

        // 5. Redirigir de vuelta a la página del producto con un mensaje de éxito.
        return back()->with('success', 'Solicitud de cambio enviada para aprobación.');
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

        return to_route('products.index');
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

        return to_route('products.index');
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

        return to_route('products.index');
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

    public function getMatch($query)
    {
        $items = Product::latest('id')
            ->where('name', 'like', "%$query%")
            ->get(['name', 'id', 'material'])
            ->take(100);
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
}
