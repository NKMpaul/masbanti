<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Commande extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'numero_commande',
        'client_id',
        'agence_id',
        'date_depot',
        'date_retrait_prevue',
        'date_retrait_effective',
        'statut',
        'mode_livraison',
        'adresse_livraison',
        'montant_total',
        'montant_paye',
        'commentaire',
    ];

    protected $casts = [
        'date_depot'             => 'datetime',
        'date_retrait_prevue'    => 'datetime',
        'date_retrait_effective' => 'datetime',
        'montant_total'          => 'decimal:2',
        'montant_paye'           => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($commande) {
            $commande->numero_commande = 'MSB-' . date('Y') . '-' . str_pad(
                Commande::withTrashed()->count() + 1, 5, '0', STR_PAD_LEFT
            );
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class);
    }

    public function articles()
    {
        return $this->hasMany(ArticleCommande::class);
    }
}