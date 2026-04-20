<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendDailyReportCommand extends Command
{
    // La commande qu'on tapera dans le terminal
    protected $signature = 'masbanti:send-daily-report';

    protected $description = 'Génère le PDF du CA du jour et l\'envoie par e-mail au Super Admin';

   public function handle()
{
    $aujourdhui = \Carbon\Carbon::today();

    // 📊 1. CA du jour basé sur tes vraies factures (Somme du montant TTC)
    $caDuJour = Facture::whereDate('created_at', $aujourdhui)
        ->sum('montant_ttc');

    // 📦 2. Nombre de commandes créées aujourd'hui
    $nbCommandes = \App\Models\Commande::whereDate('created_at', $aujourdhui)->count();

    // 🚚 3. Taux de livraison / exécution (Epic 7)
    $commandesLivrees = \App\Models\Commande::whereDate('created_at', $aujourdhui)
        ->where('statut', 'LIVREE') // Ou le nom exact de ton statut dans ta base
        ->count();

    $tauxLivraison = $nbCommandes > 0 ? ($commandesLivrees / $nbCommandes) * 100 : 0;

    // 📄 4. Génération du PDF avec DomPDF
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('factures.rapport_journalier', [
        'date' => $aujourdhui->format('d/m/Y'),
        'ca' => $caDuJour,
        'nbCommandes' => $nbCommandes,
        'tauxLivraison' => $tauxLivraison,
    ]);

    // ✉️ 5. Envoi au Super Admin
    \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($pdf, $aujourdhui) {
        $message->to('admin@masbanti.com') // 👈 Remplacer par l'email de ton choix
            ->subject("📊 Masbanti - Rapport d'Activité Journalier du " . $aujourdhui->format('d/m/Y'))
            ->html("Bonjour,<br><br>Vous trouverez en pièce jointe le rapport automatisé d'activité de l'entreprise Masbanti pour la journée.<br><br>Cordialement,<br>Le Système Masbanti Automatique.")
            ->attachData($pdf->output(), "rapport-masbanti-{$aujourdhui->format('Y-m-d')}.pdf");
    });

    $this->info('✅ Le rapport journalier a été envoyé avec succès !');
}
}
