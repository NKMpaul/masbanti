<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TransactionFidelite extends Model
{
    use HasUuids;

    protected $fillable = [
        'client_id',
        'type',
        'points',
        'motif',
        'commande_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }
}