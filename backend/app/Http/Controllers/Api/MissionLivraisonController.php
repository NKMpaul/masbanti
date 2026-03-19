<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MissionLivraison;
use Illuminate\Http\Request;

class MissionLivraisonController extends Controller
{
    public function index(Request $request)
    {
        $query = MissionLivraison::with(['commande', 'livreur']);

        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('livreur_id')) {
            $query->where('livreur_id', $request->livreur_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        return response()->json($query->orderBy('created_at', 'desc')->paginate(10));
    }

    public function store(Request $request)
    {
        $request->validate([
            'commande_id'   => 'required|exists:commandes,id',
            'livreur_id'    => 'required|exists:employes,id',
            'type'          => 'required|in:COLLECTE,LIVRAISON',
            'adresse'       => 'required|string',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'creneau_debut' => 'nullable|date',
            'creneau_fin'   => 'nullable|date',
            'commentaire'   => 'nullable|string',
        ]);

        $mission = MissionLivraison::create($request->all());

        return response()->json($mission->load(['commande', 'livreur']), 201);
    }

    public function show(string $id)
    {
        $mission = MissionLivraison::with(['commande', 'livreur'])->findOrFail($id);
        return response()->json($mission);
    }

    public function update(Request $request, string $id)
    {
        $mission = MissionLivraison::findOrFail($id);

        $request->validate([
            'statut'           => 'sometimes|in:PLANIFIEE,EN_COURS,TERMINEE,ECHOUEE',
            'signature_client' => 'sometimes|string',
            'photo_preuve'     => 'sometimes|string',
            'commentaire'      => 'sometimes|string',
        ]);

        $mission->update($request->all());

        return response()->json($mission->load(['commande', 'livreur']));
    }

    public function destroy(string $id)
    {
        MissionLivraison::findOrFail($id)->delete();
        return response()->json(['message' => 'Mission supprimée avec succès.']);
    }

    public function changerStatut(Request $request, string $id)
    {
        $mission = MissionLivraison::findOrFail($id);

        $request->validate([
            'statut' => 'required|in:PLANIFIEE,EN_COURS,TERMINEE,ECHOUEE',
        ]);

        $mission->update(['statut' => $request->statut]);

        return response()->json($mission->load(['commande', 'livreur']));
    }
}