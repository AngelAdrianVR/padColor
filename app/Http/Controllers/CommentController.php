<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
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

    
    public function show(Comment $comment)
    {
        //
    }

    
    public function edit(Comment $comment)
    {
        //
    }

    
    public function update(Request $request, Comment $comment)
    {
        // Validar también al editar
        if (preg_match('/data:image\/[^;]+;base64/', $request->body)) {
             throw ValidationException::withMessages([
                'body' => 'No se permite pegar imágenes directamente. Por favor, elimina la imagen pegada.'
            ]);
        }

        $comment->update([
            'body' => $request->body
        ]);
    }

    
    public function destroy(Comment $comment)
    {
        $comment->delete();
    }
}