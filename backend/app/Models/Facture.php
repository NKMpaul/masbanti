<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Facture extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'numero_facture',
        'commande_id',
        'client_id',
        'agence_id',
        'montant_ht',
        'tva',
        'remise_fidelite',
        'montant_ttc',
        'date_emission',
        'statut',
    ];

    protected $casts = [
        'date_emission'   => 'datetime',
        'montant_ht'      => 'decimal:2',
        'tva'             => 'decimal:2',
        'remise_fidelite' => 'decimal:2',
        'montant_ttc'     => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($facture) {
            $facture->numero_facture = 'FAC-' . date('Y') . '-' . str_pad(
                Facture::withTrashed()->count() + 1, 5, '0', STR_PAD_LEFT
            );
        });
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }
}