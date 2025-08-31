<?php

namespace App\Http\Controllers;

use App\Models\ChangeRequest;
use App\Models\Product;
use App\Models\ProductSheetTab;
use App\Models\User;
use Illuminate\Http\Request;
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
        // Eager load de la relación de media
        $product->load('media');

        $sheetStructure = ProductSheetTab::with(['fields.options' => function ($query) {
            $query->orderBy('order');
        }])
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function ($tab) {
                $activeFields = $tab->fields->where('is_active', true);
                $tab->fields_by_section = $activeFields->groupBy('section');
                unset($tab->fields); // Remove original fields collection to avoid redundancy
                return $tab;
            });

        return Inertia::render('Product/Show', [
            'product' => $product,
            'sheetStructure' => $sheetStructure,
        ]);
    }

    public function updateSheetData(Request $request, Product $product)
    {
        // 1. Validar los datos entrantes, incluyendo los archivos.
        $validated = $request->validate([
            'sheet_data' => 'nullable|array',
            'new_documents' => 'nullable|array',
            'new_documents.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,dwg,step|max:10240' // max 10MB
        ]);

        // 2. Crear la solicitud de cambio con los datos validados.
        $changeRequest = ChangeRequest::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(), // El usuario que está haciendo la solicitud.
            'data' => $validated['sheet_data'] ?? [], // Guarda los datos del formulario en el campo 'data'.
            'status' => 'pending',
        ]);

        // 3. Si hay nuevos documentos, adjuntarlos a la SOLICITUD DE CAMBIO (no al producto).
        if ($request->hasFile('new_documents')) {
            foreach ($request->file('new_documents') as $document) {
                $changeRequest->addMedia($document)->toMediaCollection('pending_documents');
            }
        }

        // 4. (Opcional) Asignar revisores automáticamente.
        $reviewers = User::whereIn('id', [3,4,5])->pluck('id'); // Ejemplo simple
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
