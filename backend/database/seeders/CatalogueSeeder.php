<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeArticle;
use App\Models\TypeService;

class CatalogueSeeder extends Seeder
{
    public function run(): void
    {
        // Types d'articles
        $articles = [
            // Vêtements
            ['nom' => 'Chemise', 'categorie' => 'VETEMENT', 'prix_base' => 25],
            ['nom' => 'Pantalon', 'categorie' => 'VETEMENT', 'prix_base' => 20],
            ['nom' => 'Robe', 'categorie' => 'VETEMENT', 'prix_base' => 35],
            ['nom' => 'Veste', 'categorie' => 'VETEMENT', 'prix_base' => 40],
            ['nom' => 'Manteau', 'categorie' => 'VETEMENT', 'prix_base' => 50],
            ['nom' => 'Costume', 'categorie' => 'VETEMENT', 'prix_base' => 70],
            ['nom' => 'Jupe', 'categorie' => 'VETEMENT', 'prix_base' => 20],
            ['nom' => 'Pull', 'categorie' => 'VETEMENT', 'prix_base' => 25],
            // Linge de maison
            ['nom' => 'Couette', 'categorie' => 'LINGE_MAISON', 'prix_base' => 60],
            ['nom' => 'Rideau', 'categorie' => 'LINGE_MAISON', 'prix_base' => 45],
            ['nom' => 'Nappe', 'categorie' => 'LINGE_MAISON', 'prix_base' => 25],
            ['nom' => 'Couverture', 'categorie' => 'LINGE_MAISON', 'prix_base' => 40],
            ['nom' => 'Drap', 'categorie' => 'LINGE_MAISON', 'prix_base' => 30],
            // Textile spécial
            ['nom' => 'Robe de mariée', 'categorie' => 'TEXTILE_SPECIAL', 'prix_base' => 200],
            ['nom' => 'Tapis', 'categorie' => 'TEXTILE_SPECIAL', 'prix_base' => 80],
            ['nom' => 'Djellaba', 'categorie' => 'TEXTILE_SPECIAL', 'prix_base' => 45],
        ];

        foreach ($articles as $article) {
            TypeArticle::firstOrCreate(
                ['nom' => $article['nom']],
                $article
            );
        }

        // Types de services
        $services = [
            ['nom' => 'Nettoyage à sec', 'description' => 'Nettoyage professionnel sans eau', 'coefficient_prix' => 1.5],
            ['nom' => 'Lavage', 'description' => 'Lavage en machine avec produits adaptés', 'coefficient_prix' => 1.0],
            ['nom' => 'Repassage', 'description' => 'Repassage soigné à la vapeur', 'coefficient_prix' => 0.8],
            ['nom' => 'Détachage', 'description' => 'Traitement spécial des taches', 'coefficient_prix' => 1.2],
            ['nom' => 'Blanchiment', 'description' => 'Blanchiment des textiles blancs', 'coefficient_prix' => 1.3],
        ];

        foreach ($services as $service) {
            TypeService::firstOrCreate(
                ['nom' => $service['nom']],
                $service
            );
        }
    }
}