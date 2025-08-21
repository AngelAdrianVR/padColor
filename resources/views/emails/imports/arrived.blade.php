<x-mail::message>
# ¡Llegada de Importación a Puerto!

Hola,

Se notifica que la importación con folio **{{ $import->id }}** del proveedor **{{ $import->supplier->name }}** ha llegado al puerto de destino.

Por favor, realice los preparativos necesarios en almacén para la recepción de la siguiente mercancía:

<x-mail::table>
| Materia Prima | Cantidad |
|:--------------|:---------|
@foreach ($import->rawMaterials as $material)
| {{ $material->name }} ({{ $material->sku }}) | {{ $material->pivot->quantity }} {{ $material->measure_unit }} |
@endforeach
</x-mail::table>

<x-mail::button :url="route('imports.index')">
Ver Detalles de la Importación
</x-mail::button>

Gracias por su atención.
</x-mail::message>
