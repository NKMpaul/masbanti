<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Client;
use App\Models\Commande;
use App\Models\Employe;
use App\Models\Facture;
use App\Models\Paiement;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function global()
    {
        $totalAgences    = Agence::count();
        $totalClients    = Client::count();
        $totalCommandes  = Commande::count();
        $totalEmployes   = Employe::count();

        $chiffreAffaires = Facture::where('statut', 'PAYEE')->sum('montant_ttc');

        $commandesParStatut = Commande::selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->get();

        $agencesPerformance = Agence::withCount('employes')
            ->with(['commandes' => function($q) {
                $q->selectRaw('agence_id, COUNT(*) as total_commandes')
                  ->groupBy('agence_id');
            }])
            ->get();

        $clientsParNiveau = Client::selectRaw('niveau_fidelite, COUNT(*) as total')
            ->groupBy('niveau_fidelite')
            ->get();

        $caParMois = Facture::where('statut', 'PAYEE')
            ->selectRaw('MONTH(date_emission) as mois, YEAR(date_emission) as annee, SUM(montant_ttc) as total')
            ->groupBy('mois', 'annee')
            ->orderBy('annee')
            ->orderBy('mois')
            ->get();

        return response()->json([
            'total_agences'        => $totalAgences,
            'total_clients'        => $totalClients,
            'total_commandes'      => $totalCommandes,
            'total_employes'       => $totalEmployes,
            'chiffre_affaires'     => $chiffreAffaires,
            'commandes_par_statut' => $commandesParStatut,
            'agences_performance'  => $agencesPerformance,
            'clients_par_niveau'   => $clientsParNiveau,
            'ca_par_mois'          => $caParMois,
        ]);
    }

    public function agence(string $id)
    {
        $agence = Agence::findOrFail($id);

        $totalCommandes  = Commande::where('agence_id', $id)->count();
        $chiffreAffaires = Facture::where('agence_id', $id)
            ->where('statut', 'PAYEE')
            ->sum('montant_ttc');

        $commandesParStatut = Commande::where('agence_id', $id)
            ->selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->get();

        $totalEmployes = Employe::where('agence_id', $id)->count();

        $employesPerformance = Employe::where('agence_id', $id)
            ->withCount('pointages')
            ->get();

        $caParMois = Facture::where('agence_id', $id)
            ->where('statut', 'PAYEE')
            ->selectRaw('MONTH(date_emission) as mois, YEAR(date_emission) as annee, SUM(montant_ttc) as total')
            ->groupBy('mois', 'annee')
            ->orderBy('annee')
            ->orderBy('mois')
            ->get();

        return response()->json([
            'agence'               => $agence,
            'total_commandes'      => $totalCommandes,
            'chiffre_affaires'     => $chiffreAffaires,
            'commandes_par_statut' => $commandesParStatut,
            'total_employes'       => $totalEmployes,
            'employes_performance' => $employesPerformance,
            'ca_par_mois'          => $caParMois,
        ]);
    }
}