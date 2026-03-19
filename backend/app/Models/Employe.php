<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Employe extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nom',
        'prenom',
        'cin',
        'telephone',
        'email',
        'poste',
        'agence_id',
        'date_embauche',
        'statut',
    ];

    protected $casts = [
        'date_embauche' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class);
    }

    public function pointages()
    {
        return $this->hasMany(Pointage::class);
    }

}