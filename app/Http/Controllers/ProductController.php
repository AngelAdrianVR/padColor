<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return inertia('Product/Index', [
            'products' => Product::latest('id')->paginate(30)
        ]);
    }

    public function create()
    {
        return inertia('Product/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:255',
            'season' => 'required|string|max:255',
            'branch' => 'required|string|max:255',
            'measure_unit' => 'required|string|max:255',
            'width' => 'required|numeric|min:0',
            'large' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'material' => 'nullable|string|max:255',
            'stock' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            // 'price' => 'required|numeric|min:0',
            'created_at' => 'nullable|date',
        ]);

        Product::create($request->all());

        return to_route('products.index');
    }

    public function show(Product $product)
    {
        //
    }

    public function edit(Product $product)
    {
        return inertia('Product/Edit', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'code' => 'required|string|max:255',
            'season' => 'required|string|max:255',
            'branch' => 'required|string|max:255',
            'measure_unit' => 'required|string|max:255',
            'width' => 'required|numeric|min:0',
            'large' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
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

        return to_route('products.index');
    }

    public function destroy(Product $product)
    {
        $product->delete();
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
        $items = Product::latest('id')->get(['name', 'id']);

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
