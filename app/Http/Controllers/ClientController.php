<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Client $client)
    {
        //
    }

    public function edit(Client $client)
    {
        //
    }

    public function update(Request $request, Client $client)
    {
        //
    }

    public function destroy(Client $client)
    {
        //
    }

    public function getAll()
    {
        $items = Client::latest('id')->get(['name', 'id']);

        return response()->json(compact('items'));
    }

    /**
     * NUEVO MÉTODO: Busca clientes que coincidan con la consulta.
     */
    public function getMatch($query)
    {
        $items = Client::latest('id')
            ->where('name', 'like', "%$query%")
            ->get(['name', 'id']) // Solo trae name e id
            ->take(30); // Limita a 30 resultados

        return response()->json(compact('items'));
    }
}
