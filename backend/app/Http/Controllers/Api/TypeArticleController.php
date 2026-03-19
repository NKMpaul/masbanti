<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TypeArticle;
use Illuminate\Http\Request;

class TypeArticleController extends Controller
{
    public function index()
    {
        return response()->json(TypeArticle::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'       => 'required|string|max:255',
            'categorie' => 'required|in:VETEMENT,LINGE_MAISON,TEXTILE_SPECIAL',
            'prix_base' => 'required|numeric|min:0',
        ]);

        $typeArticle = TypeArticle::create($request->all());
        return response()->json($typeArticle, 201);
    }

    public function show(string $id)
    {
        return response()->json(TypeArticle::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $typeArticle = TypeArticle::findOrFail($id);
        $typeArticle->update($request->all());
        return response()->json($typeArticle);
    }

    public function destroy(string $id)
    {
        TypeArticle::findOrFail($id)->delete();
        return response()->json(['message' => 'Type article supprimé avec succès.']);
    }
}