<?php

namespace Tests\Unit;

use App\Models\Agence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creer_agence(): void
    {
        $agence = Agence::create([
            'nom'       => 'Agence Test',
            'adresse'   => '123 Rue Test',
            'ville'     => 'Casablanca',
            'telephone' => '0522000000',
            'email'     => 'test@agence.com',
        ]);

        $this->assertDatabaseHas('agences', [
            'nom'   => 'Agence Test',
            'ville' => 'Casablanca',
        ]);

        $this->assertEquals('ACTIVE', $agence->fresh()->statut);    }

    public function test_soft_delete_agence(): void
    {
        $agence = Agence::create([
            'nom'       => 'Agence Delete',
            'adresse'   => '456 Rue Delete',
            'ville'     => 'Rabat',
            'telephone' => '0537000000',
            'email'     => 'delete@agence.com',
        ]);

        $agence->delete();

        $this->assertSoftDeleted('agences', ['id' => $agence->id]);
    }

    public function test_modifier_statut_agence(): void
    {
        $agence = Agence::create([
            'nom'       => 'Agence Statut',
            'adresse'   => '789 Rue Statut',
            'ville'     => 'Fes',
            'telephone' => '0535000000',
            'email'     => 'statut@agence.com',
        ]);

        $agence->update(['statut' => 'INACTIVE']);

        $this->assertEquals('INACTIVE', $agence->fresh()->statut);
    }
}