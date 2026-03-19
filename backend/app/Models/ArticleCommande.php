<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ArticleCommande extends Model
{
    use HasUuids;

    protected $fillable = [
        'commande_id',
        'type_article_id',
        'description',
        'quantite',
        'prix_unitaire',
        'code_barre_etiquette',
        'etat',
        'photo_avant',
        'photo_apres',
        'observations',
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function typeArticle()
    {
        return $this->belongsTo(TypeArticle::class);
    }
}