<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TypeService;
use Illuminate\Http\Request;

class TypeServiceController extends Controller
{
    public function index()
    {
        return response()->json(TypeService::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'              => 'required|string|max:255',
            'description'      => 'nullable|string',
            'coefficient_prix' => 'required|numeric|min:0',
        ]);

        $typeService = TypeService::create($request->all());
        return response()->json($typeService, 201);
    }

    public function show(string $id)
    {
        return response()->json(TypeService::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $typeService = TypeService::findOrFail($id);
        $typeService->update($request->all());
        return response()->json($typeService);
    }

    public function destroy(string $id)
    {
        TypeService::findOrFail($id)->delete();
        return response()->json(['message' => 'Type service supprimé avec succès.']);
    }
}