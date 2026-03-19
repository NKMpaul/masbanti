<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use Illuminate\Http\Request;

class AgenceController extends Controller
{
    public function index(Request $request)
    {
        $query = Agence::query();

        if ($request->has('ville')) {
            $query->where('ville', $request->ville);
        }

        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'       => 'required|string|max:255',
            'adresse'   => 'required|string',
            'ville'     => 'required|string',
            'telephone' => 'required|string',
            'email'     => 'required|email|unique:agences',
        ]);

        $agence = Agence::create($request->all());

        return response()->json($agence, 201);
    }

    public function show(string $id)
    {
        $agence = Agence::findOrFail($id);
        return response()->json($agence);
    }

    public function update(Request $request, string $id)
    {
        $agence = Agence::findOrFail($id);

        $request->validate([
            'nom'       => 'sometimes|string|max:255',
            'adresse'   => 'sometimes|string',
            'ville'     => 'sometimes|string',
            'telephone' => 'sometimes|string',
            'email'     => 'sometimes|email|unique:agences,email,' . $id,
        ]);

        $agence->update($request->all());

        return response()->json($agence);
    }

    public function destroy(string $id)
    {
        $agence = Agence::findOrFail($id);
        $agence->delete();

        return response()->json(['message' => 'Agence supprimée avec succès.']);
    }
}