<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use App\Models\Client;
use Illuminate\Http\Request;

class FactureController extends Controller
{
    public function index(Request $request)
    {
        $query = Facture::with(['commande', 'client', 'agence', 'paiements']);

        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('agence_id')) {
            $query->where('agence_id', $request->agence_id);
        }

        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        return response()->json($query->orderBy('created_at', 'desc')->paginate(10));
    }

    public function store(Request $request)
    {
        $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'client_id'   => 'required|exists:clients,id',
            'agence_id'   => 'required|exists:agences,id',
            'montant_ht'  => 'required|numeric|min:0',
            'tva'         => 'required|numeric|min:0',
            'remise_fidelite' => 'nullable|numeric|min:0',
        ]);

        $montantHT       = $request->montant_ht;
        $tva             = $request->tva;
        $remiseFidelite  = $request->remise_fidelite ?? 0;

        // Calcul remise fidélité selon niveau client
        $client = Client::findOrFail($request->client_id);
        if ($remiseFidelite == 0) {
            $remiseFidelite = match($client->niveau_fidelite) {
                'SILVER'   => $montantHT * 0.05,
                'GOLD'     => $montantHT * 0.10,
                'PLATINUM' => $montantHT * 0.15,
                default    => 0,
            };
        }

        $montantTTC = ($montantHT - $remiseFidelite) * (1 + $tva / 100);

        $facture = Facture::create([
            'commande_id'     => $request->commande_id,
            'client_id'       => $request->client_id,
            'agence_id'       => $request->agence_id,
            'montant_ht'      => $montantHT,
            'tva'             => $tva,
            'remise_fidelite' => $remiseFidelite,
            'montant_ttc'     => $montantTTC,
            'date_emission'   => now(),
            'statut'          => 'EN_ATTENTE',
        ]);

        return response()->json($facture->load(['commande', 'client', 'agence']), 201);
    }

    public function show(string $id)
    {
        $facture = Facture::with(['commande', 'client', 'agence', 'paiements'])->findOrFail($id);
        return response()->json($facture);
    }

    public function update(Request $request, string $id)
    {
        $facture = Facture::findOrFail($id);

        $request->validate([
            'statut' => 'sometimes|in:EN_ATTENTE,PAYEE,PARTIELLEMENT_PAYEE,ANNULEE',
        ]);

        $facture->update($request->all());

        return response()->json($facture->load(['commande', 'client', 'agence', 'paiements']));
    }

    public function destroy(string $id)
    {
        $facture = Facture::findOrFail($id);
        $facture->delete();

        return response()->json(['message' => 'Facture supprimée avec succès.']);
    }
}