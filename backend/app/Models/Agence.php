<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Agence extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'nom',
        'adresse',
        'ville',
        'telephone',
        'email',
        'horaire_ouverture',
        'latitude',
        'longitude',
        'statut',
    ];

    protected $casts = [
        'latitude'  => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function employes()
    {
        return $this->hasMany(Employe::class);
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class);
    }
}