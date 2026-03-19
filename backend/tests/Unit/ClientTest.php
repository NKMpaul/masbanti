<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    private function creerClient(): Client
    {
        $user = User::create([
            'name'     => 'Test Client',
            'email'    => 'client@test.com',
            'password' => bcrypt('password123'),
        ]);

        return Client::create([
            'user_id' => $user->id,
            'nom'     => 'Test',
            'prenom'  => 'Client',
        ]);
    }

    public function test_creer_client(): void
    {
        $client = $this->creerClient();

        $this->assertDatabaseHas('clients', [
            'nom'    => 'Test',
            'prenom' => 'Client',
        ]);

        $this->assertEquals('BRONZE', $client->fresh()->niveau_fidelite);
        $this->assertEquals(0, $client->fresh()->points_fidelite);
    }

    public function test_niveau_fidelite_silver(): void
    {
        $client = $this->creerClient();
        $client->update(['points_fidelite' => 150]);

        $this->assertEquals(150, $client->fresh()->points_fidelite);
    }

    public function test_incrementer_points(): void
    {
        $client = $this->creerClient();
        $client->increment('points_fidelite', 50);

        $this->assertEquals(50, $client->fresh()->points_fidelite);
    }

    public function test_soft_delete_client(): void
    {
        $client = $this->creerClient();
        $client->delete();

        $this->assertSoftDeleted('clients', ['id' => $client->id]);
    }
}