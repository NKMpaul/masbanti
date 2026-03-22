<?php

namespace App\Events;

use App\Models\Commande;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommandeStatutChange implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Commande $commande
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('commandes'),
            new Channel('client.' . $this->commande->client_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'statut.change';
    }

    public function broadcastWith(): array
    {
        return [
            'id'               => $this->commande->id,
            'numero_commande'  => $this->commande->numero_commande,
            'statut'           => $this->commande->statut,
            'client_id'        => $this->commande->client_id,
        ];
    }
}