<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function envoyer(
        User $user,
        string $titre,
        string $message,
        string $type,
        array $canaux = ['INTERNE'],
        array $data = []
    ): void {
        foreach ($canaux as $canal) {
            // Toujours sauvegarder en base
            Notification::create([
                'user_id' => $user->id,
                'titre'   => $titre,
                'message' => $message,
                'type'    => $type,
                'canal'   => $canal,
                'lu'      => false,
                'data'    => $data,
            ]);

            // Envoyer selon le canal
            match($canal) {
                'EMAIL' => $this->envoyerEmail($user, $titre, $message),
                'INTERNE' => null, // déjà sauvegardé en base
                default => null,
            };
        }
    }

    private function envoyerEmail(User $user, string $titre, string $message): void
    {
        try {
            Mail::send([], [], function ($mail) use ($user, $titre, $message) {
                $mail->to($user->email)
                     ->subject($titre)
                     ->html("<h2>{$titre}</h2><p>{$message}</p><br><small>Masbanti — Pressing</small>");
            });
        } catch (\Exception $e) {
            Log::error('Erreur envoi email: ' . $e->getMessage());      
             }
    }

    public function notifierChangementStatut(string $statut, $commande): void
    {
        $client = $commande->client;
        if (!$client) return;

        $messages = [
            'EN_TRAITEMENT' => [
                'titre'   => 'Votre commande est en traitement',
                'message' => "Votre commande {$commande->numero_commande} est en cours de traitement.",
            ],
            'PRETE' => [
                'titre'   => 'Votre commande est prête !',
                'message' => "Votre commande {$commande->numero_commande} est prête à être retirée.",
            ],
            'EN_LIVRAISON' => [
                'titre'   => 'Votre livreur est en route',
                'message' => "Votre commande {$commande->numero_commande} est en cours de livraison.",
            ],
            'LIVREE' => [
                'titre'   => 'Commande livrée',
                'message' => "Votre commande {$commande->numero_commande} a été livrée avec succès.",
            ],
        ];

        if (!isset($messages[$statut])) return;

        $this->envoyer(
            $client->user,
            $messages[$statut]['titre'],
            $messages[$statut]['message'],
            'COMMANDE_' . $statut,
            ['INTERNE', 'EMAIL'],
            ['commande_id' => $commande->id]
        );
    }


    public function notifierAdmins(string $titre, string $message, string $type): void
{
    $admins = \App\Models\User::role(['SUPER_ADMIN', 'ADMIN_AGENCE'])->get();

    foreach ($admins as $admin) {
        \App\Models\Notification::create([
            'user_id' => $admin->id,
            'titre'   => $titre,
            'message' => $message,
            'type'    => $type,
            'canal'   => 'INTERNE',
            'lu'      => false,
        ]);
    }
}
}