<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return inertia('Product/Index', [
            'products' => Product::with('media')->latest('id')->paginate(30)
        ]);
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
            // 'branch' => 'required|string|max:255',
            // 'measure_unit' => 'required|string|max:255',
            // 'width' => 'required|numeric|min:0',
            // 'large' => 'required|numeric|min:0',
            // 'height' => 'required|numeric|min:0',
            'material' => 'nullable|string|max:255',
            'stock' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            // 'price' => 'required|numeric|min:0',
            'created_at' => 'nullable|date',
        ]);

        $product = Product::create($request->all());

        // Guardar el archivo de portada de producto en la colección 'image'
        if ($request->hasFile('image')) {
           $product->addMediaFromRequest('image')->toMediaCollection('image');
        }

        // Guardar los archivos en la colección 'files'
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $uploadedFile) {
                $product->addMedia($uploadedFile)->toMediaCollection('files');
            }
        }

        return to_route('products.index');
    }

    public function show(Product $product)
    {
        //
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
            // 'branch' => 'required|string|max:255',
            // 'measure_unit' => 'required|string|max:255',
            // 'width' => 'required|numeric|min:0',
            // 'large' => 'required|numeric|min:0',
            // 'height' => 'required|numeric|min:0',
            'material' => 'nullable|string|max:255',
            'stock' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            // 'price' => 'required|numeric|min:0',
            'created_at' => 'nullable|date',
        ]);

        $product->update($request->all());

        // media
        // Eliminar imágenes antiguas solo si se borró desde el input y no se agregó una nueva
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
            // 'branch' => 'required|string|max:255',
            // 'measure_unit' => 'required|string|max:255',
            // 'width' => 'required|numeric|min:0',
            // 'large' => 'required|numeric|min:0',
            // 'height' => 'required|numeric|min:0',
            'material' => 'nullable|string|max:255',
            'stock' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            // 'price' => 'required|numeric|min:0',
            'created_at' => 'nullable|date',
        ]);

        $product->update($request->all());

        // media ------------
        // Eliminar imágenes antiguas solo si se proporcionan nuevas imágenes
        if ($request->hasFile('image')) {
            $product->clearMediaCollection('image');
            $product->addMediaFromRequest('image')->toMediaCollection('image');
        }

        // Guardar los archivos en la colección 'files'
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

    public function deleteFile(Request $request, $id, $fileId)
    {
        $product = Product::findOrFail($id);

        $media = $product->media()->where('id', $fileId)->first();

        if (!$media) {
            return response()->json(['message' => 'Archivo no encontrado.'], 404);
        }

        $media->delete();
    }

    public function clone(Product $product)
    {
        $clonedProduct = $product->replicate();
        
        // Si necesitas modificar el nombre, por ejemplo para evitar duplicados
        $clonedProduct->name = $product->name . ' (Copia)';

        $clonedProduct->save();
    }

    public function getAll()
    {
        $items = Product::latest('id')->get(['name', 'id', 'material']);

        return response()->json(compact('items'));
    }

    public function getMatches(Request $request)
    {
        $query = $request->input('query');

        // Realiza la búsqueda correctamente
        $products = Product::where(function ($q) use ($query) {
                $q->where('code', 'like', "%{$query}%")
                ->orWhere('name', 'like', "%{$query}%")
                ->orWhere('season', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%");
            })
            ->paginate(200);

        // Devuelve los items encontrados
        return response()->json(['items' => $products], 200);
    }
}
