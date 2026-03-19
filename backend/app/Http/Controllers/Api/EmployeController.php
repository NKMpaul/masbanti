<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employe::with(['user', 'agence']);

        if ($request->has('agence_id')) {
            $query->where('agence_id', $request->agence_id);
        }

        if ($request->has('poste')) {
            $query->where('poste', $request->poste);
        }

        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('prenom', 'like', '%' . $request->search . '%')
                  ->orWhere('cin', 'like', '%' . $request->search . '%');
            });
        }

        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'          => 'required|string|max:255',
            'prenom'       => 'required|string|max:255',
            'cin'          => 'required|string|unique:employes',
            'telephone'    => 'nullable|string',
            'email'        => 'required|email|unique:users',
            'password'     => 'required|string|min:8',
            'poste'        => 'required|in:RECEPTIONNISTE,OPERATEUR_NETTOYAGE,REPASSEUR,LIVREUR,GERANT',
            'agence_id'    => 'required|exists:agences,id',
            'date_embauche'=> 'required|date',
        ]);

        $user = User::create([
            'name'     => $request->nom . ' ' . $request->prenom,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $role = $request->poste === 'GERANT' ? 'ADMIN_AGENCE' :
               ($request->poste === 'LIVREUR' ? 'LIVREUR' : 'EMPLOYE');
        $user->assignRole($role);

        $employe = Employe::create([
            'user_id'      => $user->id,
            'nom'          => $request->nom,
            'prenom'       => $request->prenom,
            'cin'          => $request->cin,
            'telephone'    => $request->telephone,
            'email'        => $request->email,
            'poste'        => $request->poste,
            'agence_id'    => $request->agence_id,
            'date_embauche'=> $request->date_embauche,
        ]);

        return response()->json($employe->load(['user', 'agence']), 201);
    }

    public function show(string $id)
    {
        $employe = Employe::with(['user', 'agence', 'pointages'])->findOrFail($id);
        return response()->json($employe);
    }

    public function update(Request $request, string $id)
    {
        $employe = Employe::findOrFail($id);

        $request->validate([
            'nom'       => 'sometimes|string|max:255',
            'prenom'    => 'sometimes|string|max:255',
            'telephone' => 'sometimes|string',
            'poste'     => 'sometimes|in:RECEPTIONNISTE,OPERATEUR_NETTOYAGE,REPASSEUR,LIVREUR,GERANT',
            'statut'    => 'sometimes|in:ACTIF,INACTIF,EN_CONGE',
            'agence_id' => 'sometimes|exists:agences,id',
        ]);

        $employe->update($request->all());

        return response()->json($employe->load(['user', 'agence']));
    }

    public function destroy(string $id)
    {
        $employe = Employe::findOrFail($id);
        $employe->delete();

        return response()->json(['message' => 'Employé supprimé avec succès.']);
    }
}