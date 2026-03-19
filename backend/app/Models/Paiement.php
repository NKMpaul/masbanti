<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Paiement extends Model
{
    use HasUuids;

    protected $fillable = [
        'facture_id',
        'montant',
        'mode_paiement',
        'date_paiement',
        'reference',
    ];

    protected $casts = [
        'date_paiement' => 'datetime',
        'montant'       => 'decimal:2',
    ];

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }
}