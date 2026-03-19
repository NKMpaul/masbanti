<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MissionLivraison extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'commande_id',
        'livreur_id',
        'type',
        'adresse',
        'latitude',
        'longitude',
        'creneau_debut',
        'creneau_fin',
        'statut',
        'signature_client',
        'photo_preuve',
        'commentaire',
    ];

    protected $casts = [
        'creneau_debut' => 'datetime',
        'creneau_fin'   => 'datetime',
        'latitude'      => 'decimal:8',
        'longitude'     => 'decimal:8',
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function livreur()
    {
        return $this->belongsTo(Employe::class, 'livreur_id');
    }
}