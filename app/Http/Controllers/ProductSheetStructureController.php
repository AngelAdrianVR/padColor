<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductSheetTab;
use App\Models\ProductSheetField;
use App\Models\ProductSheetFieldOption;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class ProductSheetStructureController extends Controller
{
    // /**
    //  * Proteger todas las rutas de este controlador con el permiso.
    //  */
    // public function __construct()
    // {
    //     $this->middleware('can:Gestionar estructura de ficha técnica');
    // }

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:product_sheet_tabs,slug',
        ]);

        $tab = ProductSheetTab::create($validated);

        // Crear el permiso asociado
        Permission::create([
            'name' => 'Ver información de ' . strtolower($tab->name) . ' en fichas técnicas',
            'category' => 'Productos',
            'guard_name' => 'web',
        ]);

        return back()->with('success', 'Pestaña y permiso creados correctamente.');
    }

    public function updateTab(Request $request, ProductSheetTab $tab)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:product_sheet_tabs,slug,' . $tab->id,
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        // Buscar el permiso antiguo antes de actualizar el nombre de la pestaña
        $oldPermissionName = 'Ver información de ' . strtolower($tab->name) . ' en fichas técnicas';
        $permission = Permission::where('name', $oldPermissionName)->first();

        $tab->update($validated);

        // Actualizar el nombre del permiso si se encontró
        if ($permission) {
            $permission->name = 'Ver información de ' . strtolower($tab->name) . ' en fichas técnicas';
            $permission->save();
        }

        return back()->with('success', 'Pestaña y permiso actualizados correctamente.');
    }

    public function destroyTab(ProductSheetTab $tab)
    {
        // Eliminar el permiso asociado antes de eliminar la pestaña
        $permissionName = 'Ver información de ' . strtolower($tab->name) . ' en fichas técnicas';
        Permission::where('name', $permissionName)->delete();

        $tab->delete();
        return back()->with('success', 'Pestaña y permiso eliminados correctamente.');
    }

    // --- MÉTODOS PARA GESTIONAR CAMPOS (FIELDS) ---

    public function storeField(Request $request)
    {
        $request->validate([
            'tab_id' => 'required|exists:product_sheet_tabs,id',
            'label' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:product_sheet_fields,slug',
            'section' => 'required|string|max:255',
            'type' => 'required|string|in:text,textarea,select,radio,multicheckbox,checklist,file',
            'icon' => 'nullable|string|max:255', // <-- NUEVO
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
            'type' => 'required|string|in:text,textarea,select,radio,multicheckbox,checklist,file',
            'is_active' => 'boolean',
            'order' => 'integer',
            'icon' => 'nullable|string|max:255', // <-- NUEVO
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
