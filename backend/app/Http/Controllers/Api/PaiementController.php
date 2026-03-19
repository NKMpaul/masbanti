<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Models\Facture;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    public function index(Request $request)
    {
        $query = Paiement::with('facture');

        if ($request->has('facture_id')) {
            $query->where('facture_id', $request->facture_id);
        }

        return response()->json($query->orderBy('created_at', 'desc')->paginate(10));
    }

    public function store(Request $request)
    {
        $request->validate([
            'facture_id'    => 'required|exists:factures,id',
            'montant'       => 'required|numeric|min:0',
            'mode_paiement' => 'required|in:ESPECES,CARTE_BANCAIRE,PAIEMENT_MOBILE,VIREMENT',
            'reference'     => 'nullable|string',
        ]);

        $paiement = Paiement::create([
            'facture_id'    => $request->facture_id,
            'montant'       => $request->montant,
            'mode_paiement' => $request->mode_paiement,
            'date_paiement' => now(),
            'reference'     => $request->reference,
        ]);

        // Mettre à jour le statut de la facture
        $facture       = Facture::with('paiements')->findOrFail($request->facture_id);
        $totalPaye     = $facture->paiements->sum('montant');

        if ($totalPaye >= $facture->montant_ttc) {
            $facture->update(['statut' => 'PAYEE']);
        } elseif ($totalPaye > 0) {
            $facture->update(['statut' => 'PARTIELLEMENT_PAYEE']);
        }

        return response()->json($paiement->load('facture'), 201);
    }

    public function show(string $id)
    {
        return response()->json(Paiement::with('facture')->findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $paiement = Paiement::findOrFail($id);
        $paiement->update($request->all());
        return response()->json($paiement->load('facture'));
    }

    public function destroy(string $id)
    {
        Paiement::findOrFail($id)->delete();
        return response()->json(['message' => 'Paiement supprimé avec succès.']);
    }
}