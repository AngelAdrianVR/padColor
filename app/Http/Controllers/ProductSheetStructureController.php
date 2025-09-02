<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductSheetTab;
use App\Models\ProductSheetField;
use App\Models\ProductSheetFieldOption;
use Inertia\Inertia;

class ProductSheetStructureController extends Controller
{
    /**
     * Proteger todas las rutas de este controlador con el permiso.
     */
    public function __construct()
    {
        $this->middleware('can:Gestionar estructura de ficha técnica');
    }

    /**
     * Muestra la página principal de gestión de la estructura.
     */
    public function index()
    {
        $tabs = ProductSheetTab::with(['fields.options' => function ($query) {
            $query->orderBy('order');
        }])->orderBy('order')->get();

        return Inertia::render('ProductSheet/Structure', [
            'tabs' => $tabs
        ]);
    }

    // --- MÉTODOS PARA GESTIONAR PESTAÑAS (TABS) ---

    public function storeTab(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:product_sheet_tabs,slug',
        ]);
        ProductSheetTab::create($request->all());
        return back()->with('success', 'Pestaña creada correctamente.');
    }

    public function updateTab(Request $request, ProductSheetTab $tab)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:product_sheet_tabs,slug,' . $tab->id,
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);
        $tab->update($request->all());
        return back()->with('success', 'Pestaña actualizada correctamente.');
    }

    public function destroyTab(ProductSheetTab $tab)
    {
        $tab->delete();
        return back()->with('success', 'Pestaña eliminada correctamente.');
    }

    // --- MÉTODOS PARA GESTIONAR CAMPOS (FIELDS) ---

    public function storeField(Request $request)
    {
        $request->validate([
            'tab_id' => 'required|exists:product_sheet_tabs,id',
            'label' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:product_sheet_fields,slug',
            'section' => 'required|string|max:255',
            'type' => 'required|string|in:text,textarea,select,radio,multicheckbox,checklist',
        ]);
        ProductSheetField::create($request->all());
        return back()->with('success', 'Campo creado correctamente.');
    }

    public function updateField(Request $request, ProductSheetField $field)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:product_sheet_fields,slug,' . $field->id,
            'section' => 'required|string|max:255',
            'type' => 'required|string|in:text,textarea,select,radio,multicheckbox,checklist',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);
        $field->update($request->all());
        return back()->with('success', 'Campo actualizado correctamente.');
    }

    public function destroyField(ProductSheetField $field)
    {
        $field->delete();
        return back()->with('success', 'Campo eliminado correctamente.');
    }

    // --- MÉTODOS PARA GESTIONAR OPCIONES (OPTIONS) ---

    public function storeOption(Request $request)
    {
        $request->validate([
            'field_id' => 'required|exists:product_sheet_fields,id',
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);
        ProductSheetFieldOption::create($request->all());
        return back()->with('success', 'Opción creada correctamente.');
    }

    public function updateOption(Request $request, ProductSheetFieldOption $option)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'order' => 'integer',
        ]);
        $option->update($request->all());
        return back()->with('success', 'Opción actualizada correctamente.');
    }

    public function destroyOption(ProductSheetFieldOption $option)
    {
        $option->delete();
        return back()->with('success', 'Opción eliminada correctamente.');
    }
}