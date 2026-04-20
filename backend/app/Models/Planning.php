<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Planning extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'date_planning',
        'heure_debut',
        'heure_fin',
        'statut',
        'notes',
    ];

    // Génération automatique de l'UUID à la création
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // Relation : Un planning appartient à un employé (User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}