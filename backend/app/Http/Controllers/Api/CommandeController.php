<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use Illuminate\Http\Request;

class CommandeController extends Controller
{
    public function index(Request $request)
{
    $query = Commande::with(['client', 'agence', 'articles']);

    // Si c'est un client, il ne voit que ses commandes
    if ($request->user()->hasRole('CLIENT')) {
        $client = \App\Models\Client::where('user_id', $request->user()->id)->first();
        if ($client) {
            $query->where('client_id', $client->id);
        } else {
            // Nouveau client sans commandes
            return response()->json(['data' => [], 'total' => 0]);
        }
    }

    if ($request->has('statut')) {
        $query->where('statut', $request->statut);
    }

    if ($request->has('agence_id')) {
        $query->where('agence_id', $request->agence_id);
    }

    return response()->json($query->orderBy('created_at', 'desc')->paginate(10));
}

    public function store(Request $request)
    {
        $request->validate([
            'client_id'          => 'required|exists:clients,id',
            'agence_id'          => 'required|exists:agences,id',
            'date_depot'         => 'required|date',
            'date_retrait_prevue'=> 'nullable|date',
            'mode_livraison'     => 'required|in:EN_AGENCE,LIVRAISON_DOMICILE',
            'adresse_livraison'  => 'nullable|string',
            'commentaire'        => 'nullable|string',
            'articles'           => 'required|array|min:1',
            'articles.*.type_article_id' => 'required|exists:type_articles,id',
            'articles.*.quantite'        => 'required|integer|min:1',
            'articles.*.prix_unitaire'   => 'required|numeric|min:0',
            'articles.*.etat'            => 'nullable|in:NORMAL,TACHE,DECOLORE,ABIME',
        ]);

        $commande = Commande::create($request->except('articles'));
        $notificationService = new \App\Services\NotificationService();
        $notificationService->notifierAdmins(
            'Nouvelle commande reçue',
            "La commande {$commande->numero_commande} vient d'être créée.",
            'COMMANDE_CREEE'
        );

        $notificationService = new \App\Services\NotificationService();
        $notificationService->notifierAdmins(
            'Nouvelle commande reçue',
            "La commande {$commande->numero_commande} vient d'être créée.",
            'COMMANDE_CREEE'
        );

        $montantTotal = 0;
        foreach ($request->articles as $article) {
            $commande->articles()->create($article);
            $montantTotal += $article['prix_unitaire'] * $article['quantite'];
        }

        $commande->update(['montant_total' => $montantTotal]);

        return response()->json($commande->load(['client', 'agence', 'articles']), 201);
    }

    public function show(string $id)
    {
        $commande = Commande::with(['client', 'agence', 'articles.typeArticle'])->findOrFail($id);
        return response()->json($commande);
    }

    public function update(Request $request, string $id)
    {
        $commande = Commande::findOrFail($id);

        $request->validate([
            'statut'          => 'sometimes|in:CREEE,EN_ATTENTE,COLLECTEE,RECUE_AGENCE,EN_TRAITEMENT,PRETE,EN_LIVRAISON,LIVREE,RETIREE_AGENCE,ANNULEE',
            'commentaire'     => 'sometimes|string',
            'montant_paye'    => 'sometimes|numeric|min:0',
            'adresse_livraison' => 'sometimes|string',
        ]);

        $commande->update($request->all());

        return response()->json($commande->load(['client', 'agence', 'articles']));
    }

    public function destroy(string $id)
    {
        $commande = Commande::findOrFail($id);
        $commande->delete();

        return response()->json(['message' => 'Commande supprimée avec succès.']);
    }

   public function changerStatut(Request $request, string $id)
    {
        $commande = Commande::with(['client.user'])->findOrFail($id);

        $request->validate([
            'statut' => 'required|in:CREEE,EN_ATTENTE,COLLECTEE,RECUE_AGENCE,EN_TRAITEMENT,PRETE,EN_LIVRAISON,LIVREE,RETIREE_AGENCE,ANNULEE',
        ]);

        $commande->update(['statut' => $request->statut]);

        // Notification automatique
        $notificationService = new \App\Services\NotificationService();
        $notificationService->notifierChangementStatut($request->statut, $commande);

        // Broadcast WebSocket
        broadcast(new \App\Events\CommandeStatutChange($commande))->toOthers();

        return response()->json($commande);

        if ($request->statut === 'PRETE') {
        $notificationService->notifierAdmins(
        'Commande prête',
        "La commande {$commande->numero_commande} est prête à être retirée.",
        'COMMANDE_PRETE'
    );
}
    }


    public function suivi(string $numero)
    {
        $commande = Commande::with(['agence', 'articles.typeArticle'])
            ->where('numero_commande', $numero)
            ->first();

        if (!$commande) {
            return response()->json(['message' => 'Commande introuvable.'], 404);
        }

        return response()->json($commande);
    }
}