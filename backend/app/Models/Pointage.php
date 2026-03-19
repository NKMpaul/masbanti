<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Pointage extends Model
{
    use HasUuids;

    protected $fillable = [
        'employe_id',
        'date_pointage',
        'heure_arrivee',
        'heure_depart',
        'statut',
    ];

    protected $casts = [
        'date_pointage' => 'date',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }
}