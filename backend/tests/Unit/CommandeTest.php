<?php

namespace Tests\Unit;

use App\Models\Agence;
use App\Models\Client;
use App\Models\Commande;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommandeTest extends TestCase
{
    use RefreshDatabase;

    private function creerCommande(): Commande
    {
        $user = User::create([
            'name'     => 'Test User',
            'email'    => 'user@test.com',
            'password' => bcrypt('password123'),
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'nom'     => 'Test',
            'prenom'  => 'User',
        ]);

        $agence = Agence::create([
            'nom'       => 'Agence Test',
            'adresse'   => '123 Rue Test',
            'ville'     => 'Casablanca',
            'telephone' => '0522000000',
            'email'     => 'test@agence.com',
        ]);

        return Commande::create([
            'client_id'      => $client->id,
            'agence_id'      => $agence->id,
            'date_depot'     => now(),
            'mode_livraison' => 'EN_AGENCE',
            'statut'         => 'CREEE',
        ]);
    }

    public function test_creer_commande(): void
    {
        $commande = $this->creerCommande();

        $this->assertDatabaseHas('commandes', [
            'id'     => $commande->id,
            'statut' => 'CREEE',
        ]);

        $this->assertStringStartsWith('MSB-', $commande->numero_commande);
    }

    public function test_changer_statut_commande(): void
    {
        $commande = $this->creerCommande();
        $commande->update(['statut' => 'EN_TRAITEMENT']);

        $this->assertEquals('EN_TRAITEMENT', $commande->fresh()->statut);
    }

    public function test_numero_commande_auto_genere(): void
    {
        $commande = $this->creerCommande();

        $this->assertNotNull($commande->numero_commande);
        $this->assertStringContainsString(date('Y'), $commande->numero_commande);
    }

    public function test_soft_delete_commande(): void
    {
        $commande = $this->creerCommande();
        $commande->delete();

        $this->assertSoftDeleted('commandes', ['id' => $commande->id]);
    }
}