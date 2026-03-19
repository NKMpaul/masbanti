<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Client extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nom',
        'prenom',
        'telephone',
        'adresse',
        'ville',
        'points_fidelite',
        'niveau_fidelite',
        'agence_principale_id',
        'notifications_email',
        'notifications_sms',
        'notifications_push',
        'mode_livraison_prefere',
        'code_parrainage',
    ];

    protected $casts = [
        'notifications_email' => 'boolean',
        'notifications_sms'   => 'boolean',
        'notifications_push'  => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agencePrincipale()
    {
        return $this->belongsTo(Agence::class, 'agence_principale_id');
    }
}