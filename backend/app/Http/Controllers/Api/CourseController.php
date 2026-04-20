<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commande; // ou Course selon ton PDF
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // Changer le statut de la livraison (Épic 7)
    public function changerStatut(Request $request, $id)
    {
        $request->validate([
            'statut' => 'required|in:EN_COURS,ARRIVE,LIVRE,ECHEC',
            'motif_echec' => 'nullable|string'
        ]);

        $course = Commande::findOrFail($id);

        // Mise à jour du statut
        $course->statut = $request->statut;

        if ($request->statut === 'ECHEC') {
            $course->motif_echec = $request->motif_echec;
        }

        $course->save();

        // 📝 Optionnel : Enregistrer l'action dans les Audit Logs (vu qu'on l'a codé plus haut !)
        
        return response()->json([
            'message' => 'Statut de la course mis à jour avec succès !',
            'course' => $course
        ]);
    }
}