<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Planning;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    // 📅 1. Récupérer la liste des plannings (avec les infos de l'employé)
    public function index()
    {
        // On utilise eager loading (with) pour éviter la lenteur N+1 !
        $plannings = Planning::with('user')
            ->orderBy('date_planning', 'asc')
            ->get();

        return response()->json($plannings);
    }

    // ➕ 2. Créer un nouvel horaire pour un employé
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date_planning' => 'required|date',
            'heure_debut' => 'required',
            'heure_fin' => 'required',
            'statut' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $planning = Planning::create($validated);

        return response()->json([
            'message' => '✅ Planning enregistré avec succès !',
            'data' => $planning
        ], 201);
    }

    // ✏️ 3. Modifier un planning existant
    public function update(Request $request, string $id)
    {
        $planning = Planning::findOrFail($id);

        $validated = $request->validate([
            'date_planning' => 'date',
            'heure_debut' => 'string',
            'heure_fin' => 'string',
            'statut' => 'string',
            'notes' => 'nullable|string',
        ]);

        $planning->update($validated);

        return response()->json([
            'message' => '✅ Planning mis à jour !',
            'data' => $planning
        ]);
    }

    // ❌ 4. Supprimer un planning
    public function destroy(string $id)
    {
        $planning = Planning::findOrFail($id);
        $planning->delete();

        return response()->json(['message' => '🗑️ Planning supprimé !']);
    }
}