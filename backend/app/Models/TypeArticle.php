<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TypeArticle extends Model
{
    use HasUuids;

    protected $fillable = [
        'nom',
        'categorie',
        'prix_base',
    ];
}