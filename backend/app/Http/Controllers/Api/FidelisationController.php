<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\TransactionFidelite;
use Illuminate\Http\Request;

class FidelisationController extends Controller
{
    public function points(string $clientId)
    {
        $client = Client::findOrFail($clientId);

        return response()->json([
            'client'          => $client,
            'points_fidelite' => $client->points_fidelite,
            'niveau_fidelite' => $client->niveau_fidelite,
            'transactions'    => TransactionFidelite::where('client_id', $clientId)
                                    ->orderBy('created_at', 'desc')
                                    ->get(),
        ]);
    }

    public function crediterPoints(Request $request)
    {
        $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'points'      => 'required|integer|min:1',
            'motif'       => 'nullable|string',
            'commande_id' => 'nullable|exists:commandes,id',
        ]);

        $client = Client::findOrFail($request->client_id);

        TransactionFidelite::create([
            'client_id'   => $request->client_id,
            'type'        => 'GAIN',
            'points'      => $request->points,
            'motif'       => $request->motif ?? 'Crédit manuel',
            'commande_id' => $request->commande_id,
        ]);

        $client->increment('points_fidelite', $request->points);

        // Mise à jour du niveau
        $this->mettreAJourNiveau($client);

        return response()->json([
            'message'         => 'Points crédités avec succès.',
            'points_fidelite' => $client->fresh()->points_fidelite,
            'niveau_fidelite' => $client->fresh()->niveau_fidelite,
        ]);
    }

    public function debiterPoints(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'points'    => 'required|integer|min:1',
            'motif'     => 'nullable|string',
        ]);

        $client = Client::findOrFail($request->client_id);

        if ($client->points_fidelite < $request->points) {
            return response()->json([
                'message' => 'Points insuffisants.',
            ], 422);
        }

        TransactionFidelite::create([
            'client_id' => $request->client_id,
            'type'      => 'DEBIT',
            'points'    => $request->points,
            'motif'     => $request->motif ?? 'Débit manuel',
        ]);

        $client->decrement('points_fidelite', $request->points);
        $this->mettreAJourNiveau($client);

        return response()->json([
            'message'         => 'Points débités avec succès.',
            'points_fidelite' => $client->fresh()->points_fidelite,
            'niveau_fidelite' => $client->fresh()->niveau_fidelite,
        ]);
    }

    public function parrainage(Request $request)
    {
        $request->validate([
            'code_parrainage' => 'required|string',
            'client_id'       => 'required|exists:clients,id',
        ]);

        $parrain = Client::where('code_parrainage', $request->code_parrainage)->first();

        if (!$parrain) {
            return response()->json(['message' => 'Code de parrainage invalide.'], 422);
        }

        if ($parrain->id === $request->client_id) {
            return response()->json(['message' => 'Vous ne pouvez pas vous parrainer vous-même.'], 422);
        }

        // 50 points pour le parrain
        $this->crediterPoints(new Request([
            'client_id' => $parrain->id,
            'points'    => 50,
            'motif'     => 'Parrainage',
        ]));

        // 50 points pour le filleul
        $this->crediterPoints(new Request([
            'client_id' => $request->client_id,
            'points'    => 50,
            'motif'     => 'Parrainage',
        ]));

        return response()->json(['message' => 'Parrainage validé ! 50 points crédités.']);
    }

    private function mettreAJourNiveau(Client $client): void
    {
        $points = $client->points_fidelite;

        $niveau = match(true) {
            $points >= 600 => 'PLATINUM',
            $points >= 300 => 'GOLD',
            $points >= 100 => 'SILVER',
            default        => 'BRONZE',
        };

        $client->update(['niveau_fidelite' => $niveau]);
    }

    public function genererCodeParrainage(string $clientId)
    {
        $client = Client::findOrFail($clientId);

        if (!$client->code_parrainage) {
            $client->update([
                'code_parrainage' => strtoupper(substr(md5($clientId . time()), 0, 8))
            ]);
        }

        return response()->json([
            'code_parrainage' => $client->code_parrainage,
        ]);
    }
}