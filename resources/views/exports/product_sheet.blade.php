<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="2" style="font-size: 18px; font-weight: bold;">Ficha Técnica: {{ $product->name }}</th>
            </tr>
            <tr>
                <th colspan="2">Código: {{ $product->code }}</th>
            </tr>
            <tr>
                <th colspan="2"></th> <!-- Espacio en blanco -->
            </tr>
        </thead>
        <tbody>
            @foreach ($sheetStructure as $tab)
                <tr>
                    <td colspan="2" style="font-size: 16px; font-weight: bold; background-color: #f2f2f2;">{{ $tab->name }}</td>
                </tr>
                @foreach ($tab->fields_by_section as $sectionName => $fields)
                    <tr>
                        <td colspan="2" style="font-size: 14px; font-weight: bold; background-color: #fafafa;">{{ str_replace('_', ' ', ucfirst($sectionName)) }}</td>
                    </tr>
                    @foreach ($fields as $field)
                        <tr>
                            <td style="font-weight: bold;">{{ $field['label'] }}</td>
                            <td>
                                @php
                                    $value = $product->sheet_data[$field['slug']] ?? null;
                                    
                                    if (is_null($value) || $value === '') {
                                        echo '-';
                                    } elseif (is_array($value)) {
                                        // --- INICIO DE LA CORRECCIÓN ---
                                        // Lógica robusta para campos de selección múltiple.
                                        if (empty($value)) {
                                            echo '-';
                                        } else {
                                            $optionsCollection = collect($field['options']);
                                            $displayValues = collect($value)->map(function ($selectedValue) use ($optionsCollection) {
                                                $foundOption = $optionsCollection->firstWhere('value', $selectedValue);
                                                return $foundOption ? $foundOption['label'] : $selectedValue;
                                            })->implode(', ');
                                            echo $displayValues;
                                        }
                                        // --- FIN DE LA CORRECCIÓN ---
                                    } elseif (!empty($field['options'])) {
                                        // Lógica para campos de selección simple (select, radio).
                                        $option = collect($field['options'])->firstWhere('value', $value);
                                        echo $option ? $option['label'] : ($value ?: '-');
                                    } else {
                                        // Para campos de texto simples.
                                        echo $value;
                                    }
                                @endphp
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                 <tr>
                    <td colspan="2"></td> <!-- Espacio en blanco -->
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>